<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PayPalController extends Controller
{
    protected function baseUrl(): string
    {
        $mode = config('paypal.mode', 'sandbox');
        return $mode === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
    }

    protected function getAccessToken(): ?string
    {
        $clientId = config('paypal.client_id');
        $secret = config('paypal.secret');
        if (!$clientId || !$secret) return null;

        $resp = Http::asForm()
            ->withBasicAuth($clientId, $secret)
            ->post($this->baseUrl().'/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);
        if (!$resp->ok()) return null;
        return $resp->json('access_token');
    }

    public function createOrder(Request $request)
    {
        $user = $request->user();
        $cartItems = CartItem::query()
            ->where('user_id', $user->id)
            ->with(['product','variant'])
            ->get();
        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
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
        if ($amountCents < 1) {
            return response()->json(['message' => 'Invalid amount'], 400);
        }
        $currency = strtoupper(config('stripe.currency', 'usd'));

        $token = $this->getAccessToken();
        if (!$token) return response()->json(['message' => 'PayPal not configured'], 500);

        $resp = Http::withToken($token)
            ->post($this->baseUrl().'/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'amount' => [
                        'currency_code' => $currency,
                        'value' => number_format($amountCents / 100, 2, '.', ''),
                    ],
                ]],
            ]);
        if (!$resp->ok()) {
            return response()->json(['message' => 'Failed to create PayPal order', 'details' => $resp->json()], 500);
        }
        return response()->json([
            'id' => $resp->json('id'),
            'status' => $resp->json('status'),
        ]);
    }

    public function capture(Request $request)
    {
        $data = $request->validate([
            'orderId' => ['required', 'string'],
            'address_id' => ['required', 'integer', 'exists:addresses,id'],
        ]);

        $user = $request->user();
        $address = $user->addresses()->where('id', $data['address_id'])->first();
        if (!$address) {
            return response()->json(['message' => 'Invalid address selected'], 422);
        }

        $token = $this->getAccessToken();
        if (!$token) return response()->json(['message' => 'PayPal not configured'], 500);

        $resp = Http::withToken($token)
            ->post($this->baseUrl()."/v2/checkout/orders/{$data['orderId']}/capture");
        if (!$resp->ok()) {
            return response()->json(['message' => 'Failed to capture PayPal order', 'details' => $resp->json()], 500);
        }
        $capture = $resp->json();
        if (($capture['status'] ?? '') !== 'COMPLETED') {
            return response()->json(['message' => 'Payment not completed', 'details' => $capture], 422);
        }

        $amountValue = $capture['purchase_units'][0]['payments']['captures'][0]['amount']['value'] ?? null;
        $currency = $capture['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'] ?? null;
        $transactionId = $capture['purchase_units'][0]['payments']['captures'][0]['id'] ?? $data['orderId'];
        $amountCents = $amountValue !== null ? (int) round(((float) $amountValue) * 100) : 0;

        $cartItems = CartItem::query()
            ->where('user_id', $user->id)
            ->with(['product','variant'])
            ->get();
        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 400);
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

        PaymentInfo::updateOrCreate(
            ['transaction_id' => $transactionId],
            [
                'order_id' => $order->id,
                'provider' => 'paypal',
                'amount' => $order->total_cents ?: $amountCents,
                'status' => 'succeeded',
                'paid_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'Payment successful',
            'order_id' => $order->id,
        ]);
    }
}

