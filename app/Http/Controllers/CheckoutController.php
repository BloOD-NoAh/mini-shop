<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function page()
    {
        $user = auth()->user();

        $cartItems = CartItem::query()
            ->where('user_id', $user->id)
            ->with('product')
            ->get();

        $subtotal = (int) $cartItems->sum(fn ($i) => $i->product->price_cents * $i->quantity);
        $publishableKey = config('stripe.key');

        return view('checkout.index', [
            'subtotal_cents' => $subtotal,
            'stripe_key' => $publishableKey,
            'has_stripe' => $publishableKey && class_exists(\Stripe\StripeClient::class),
        ]);
    }

    public function checkout(Request $request): JsonResponse
    {
        $user = auth()->user();

        $cartItems = CartItem::query()
            ->where('user_id', $user->id)
            ->with('product')
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 422);
        }

        $amount = (int) $cartItems->sum(fn ($i) => $i->product->price_cents * $i->quantity);

        $secretKey = config('stripe.secret');

        if ($secretKey && class_exists(\Stripe\StripeClient::class)) {
            try {
                $stripe = new \Stripe\StripeClient($secretKey);
                $pi = $stripe->paymentIntents->create([
                    'amount' => $amount,
                    'currency' => 'usd',
                    'metadata' => [
                        'user_id' => (string) $user->id,
                    ],
                    'automatic_payment_methods' => ['enabled' => true],
                ]);

                return response()->json([
                    'client_secret' => $pi->client_secret,
                    'amount' => $amount,
                ]);
            } catch (\Throwable $e) {
                // Fallback to mock if Stripe not available
            }
        }

        return response()->json([
            'mock' => true,
            'amount' => $amount,
            'message' => 'Stripe not configured; using mock payment',
        ]);
    }

    public function confirm(): JsonResponse
    {
        $user = auth()->user();

        $cartItems = CartItem::query()
            ->where('user_id', $user->id)
            ->with('product')
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 422);
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

        return response()->json([
            'message' => 'Payment confirmed',
            'order' => $order->load('items'),
        ]);
    }

    public function show(Order $order): JsonResponse
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

    public function success()
    {
        return view('checkout.success');
    }

    public function cancel()
    {
        return view('checkout.cancel');
    }
}
