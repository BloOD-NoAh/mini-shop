<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Models\PaymentInfo;
use Inertia\Response as InertiaResponse;

class CheckoutController extends Controller
{
    public function create(Request $request)
    {
        $user = $request->user();

        $cartItems = CartItem::query()
            ->where('user_id', $user->id)
            ->with(['product','variant'])
            ->get();

        if ($cartItems->isEmpty()) {
            abort(400, 'Cart is empty');
        }

        $amountCents = (int) $cartItems->sum(function ($i) {
            $variantAmount = $i->variant?->price
                ?? ($i->variant ? (($i->variant->net_price ?? 0) + ($i->variant->tax ?? 0)) : null);
            if ($variantAmount !== null) {
                return (int) round($variantAmount * 100) * (int) $i->quantity;
            }
            $price = $i->variant?->selling_price_cents
                ?? $i->variant?->price_cents
                ?? ($i->product->selling_price_cents !== null ? (int) round(((float) $i->product->selling_price_cents) * 100) : $i->product->price_cents);
            return (int) $price * (int) $i->quantity;
        });
        $currency = config('stripe.currency', 'usd');

        if ($amountCents < 1) {
            abort(400, 'Invalid amount');
        }

        // Determine enabled methods (from settings; fallback to Stripe only)
        $enabled = (array) (\App\Models\Setting::get('payment.methods.enabled') ?: []);
        $available = [];
        if (($enabled['stripe'] ?? false) && config('stripe.secret')) $available[] = 'stripe';
        if (($enabled['paypal'] ?? false) && config('paypal.client_id') && config('paypal.secret')) $available[] = 'paypal';
        if ($available === []) $available = ['stripe'];

        $method = $request->query('method');
        if (!$method || !in_array($method, $available, true)) {
            if (count($available) === 1) {
                return redirect()->route('checkout.create', ['method' => $available[0]]);
            }
            // Show method selection page
            return Inertia::render('Checkout/SelectMethod', [
                'methods' => $available,
                'amountCents' => $amountCents,
                'currency' => $currency,
            ]);
        }

        $cart = $cartItems->map(function ($i) {
            $variantAmount = $i->variant?->price
                ?? ($i->variant ? (($i->variant->net_price ?? 0) + ($i->variant->tax ?? 0)) : null);
            $unit = $variantAmount !== null
                ? (int) round($variantAmount * 100)
                : (int) (
                    $i->variant?->selling_price_cents
                    ?? $i->variant?->price_cents
                    ?? ($i->product->selling_price_cents !== null ? (int) round(((float) $i->product->selling_price_cents) * 100) : $i->product->price_cents)
                );
            return [
                'product' => [
                    'id' => $i->product_id,
                    'name' => $i->product->name,
                ],
                'variant' => $i->variant ? [
                    'id' => $i->product_variant_id,
                    'attributes' => $i->variant->attributes,
                ] : null,
                'quantity' => (int) $i->quantity,
                'unit_price_cents' => $unit,
            ];
        })->values();

        $addresses = $user->addresses()->get(['id','full_name','line1','line2','city','state','postal_code','country','phone','is_default']);

        if ($method === 'paypal') {
            if ($request->wantsJson()) {
                return response()->json([
                    'amountCents' => $amountCents,
                    'currency' => $currency,
                    'cart' => $cart,
                    'addresses' => $addresses,
                ]);
            }
            return Inertia::render('Checkout/PayPal', [
                'amountCents' => $amountCents,
                'currency' => $currency,
                'cart' => $cart,
                'addresses' => $addresses,
            ]);
        }

        // Default Stripe flow
        Stripe::setApiKey(config('stripe.secret'));
        $intent = PaymentIntent::create([
            'amount' => $amountCents,
            'currency' => $currency,
            'metadata' => [
                'user_id' => (string) $user->id,
            ],
            'automatic_payment_methods' => ['enabled' => true],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'clientSecret' => $intent->client_secret,
                'amountCents' => $amountCents,
                'currency' => $currency,
                'cart' => $cart,
                'addresses' => $addresses,
            ]);
        }

        return Inertia::render('Checkout/Pay', [
            'clientSecret' => $intent->client_secret,
            'amountCents' => $amountCents,
            'currency' => $currency,
            'cart' => $cart,
            'addresses' => $addresses,
        ]);
    }

    public function confirm(Request $request)
    {
        $data = $request->validate([
            'paymentIntentId' => ['required', 'string'],
            'address_id' => ['required', 'integer', 'exists:addresses,id'],
        ]);

        $user = $request->user();
        $address = $user->addresses()->where('id', $data['address_id'])->first();
        if (!$address) {
            return redirect()->back()->withErrors(['address_id' => 'Invalid address selected']);
        }
        $cartItems = CartItem::query()
            ->where('user_id', $user->id)
            ->with(['product','variant'])
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->back()->with('status', 'Cart is empty');
        }

        $order = null;
        DB::transaction(function () use ($user, $cartItems, &$order, $address): void {
            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'paid',
                'total_cents' => 0,
                'shipping_address' => [
                    'full_name' => $address->full_name,
                    'line1' => $address->line1,
                    'line2' => $address->line2,
                    'city' => $address->city,
                    'state' => $address->state,
                    'postal_code' => $address->postal_code,
                    'country' => $address->country,
                    'phone' => $address->phone,
                ],
            ]);

            foreach ($cartItems as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'product_variant_id' => $cartItem->product_variant_id,
                    'unit_price_cents' => (int) (function() use ($cartItem) {
                        $variantAmount = $cartItem->variant?->price
                            ?? ($cartItem->variant ? (($cartItem->variant->net_price ?? 0) + ($cartItem->variant->tax ?? 0)) : null);
                        if ($variantAmount !== null) return (int) round($variantAmount * 100);
                        return (int) (
                            $cartItem->variant?->selling_price_cents
                            ?? $cartItem->variant?->price_cents
                            ?? ($cartItem->product->selling_price_cents !== null ? (int) round(((float) $cartItem->product->selling_price_cents) * 100) : $cartItem->product->price_cents)
                        );
                    })(),
                    'quantity' => (int) $cartItem->quantity,
                ]);
            }

            $order->recalculateTotal();
            CartItem::where('user_id', $user->id)->delete();
        });

        // Attach payment info to order if webhook already created it
        $transactionId = $data['paymentIntentId'];
        PaymentInfo::updateOrCreate(
            ['transaction_id' => $transactionId],
            [
                'order_id' => $order->id,
                'provider' => 'stripe',
                'amount' => $order->total_cents,
                'status' => 'processing',
            ]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Payment successful',
                'order_id' => $order->id,
            ]);
        }

        return redirect()->route('orders.show', $order->id)
            ->with('status', 'Payment successful');
    }

    public function show(Order $order): InertiaResponse|\Illuminate\Http\JsonResponse
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load('items.product');

        if (request()->wantsJson()) {
            return response()->json($order);
        }

        return Inertia::render('Orders/Show', [
            'order' => $order,
        ]);
    }

    public function index(): InertiaResponse
    {
        $orders = Order::query()
            ->where('user_id', auth()->id())
            ->latest('id')
            ->get(['id','status','total_cents','created_at']);

        // Append formatted total for convenience
        $orders = $orders->map(fn ($o) => [
            'id' => $o->id,
            'status' => $o->status,
            'total_cents' => (int) $o->total_cents,
            'total_formatted' => $o->total_formatted,
            'created_at' => $o->created_at?->toDateTimeString(),
        ]);

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
        ]);
    }
}
