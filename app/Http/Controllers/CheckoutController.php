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

class CheckoutController extends Controller
{
    public function create(Request $request)
    {
        $user = $request->user();

        $cartItems = CartItem::query()
            ->where('user_id', $user->id)
            ->with('product')
            ->get();

        if ($cartItems->isEmpty()) {
            abort(400, 'Cart is empty');
        }

        $amountCents = (int) $cartItems->sum(fn ($i) => $i->product->price_cents * $i->quantity);
        $currency = config('stripe.currency', 'usd');

        if ($amountCents < 1) {
            abort(400, 'Invalid amount');
        }

        Stripe::setApiKey(config('stripe.secret'));
        $intent = PaymentIntent::create([
            'amount' => $amountCents,
            'currency' => $currency,
            'metadata' => [
                'user_id' => (string) $user->id,
            ],
            'automatic_payment_methods' => ['enabled' => true],
        ]);

        $cart = $cartItems->map(fn ($i) => [
            'product' => [
                'id' => $i->product_id,
                'name' => $i->product->name,
            ],
            'quantity' => (int) $i->quantity,
            'unit_price_cents' => (int) $i->product->price_cents,
        ])->values();

        return Inertia::render('Checkout/Pay', [
            'clientSecret' => $intent->client_secret,
            'amountCents' => $amountCents,
            'currency' => $currency,
            'cart' => $cart,
        ]);
    }

    public function confirm(Request $request)
    {
        $data = $request->validate([
            'paymentIntentId' => ['required', 'string'],
        ]);

        $user = $request->user();
        $cartItems = CartItem::query()
            ->where('user_id', $user->id)
            ->with('product')
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->back()->with('status', 'Cart is empty');
        }

        $order = null;
        DB::transaction(function () use ($user, $cartItems, &$order): void {
            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'paid',
                'total_cents' => 0,
            ]);

            foreach ($cartItems as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'unit_price_cents' => (int) $cartItem->product->price_cents,
                    'quantity' => (int) $cartItem->quantity,
                ]);
            }

            $order->recalculateTotal();
            CartItem::where('user_id', $user->id)->delete();
        });

        return redirect()->route('orders.show', $order->id)
            ->with('status', 'Payment successful');
    }

    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load('items.product');

        if (request()->wantsJson()) {
            return response()->json($order);
        }

        return response()->view('orders.show', [
            'order' => $order,
        ]);
    }
}
