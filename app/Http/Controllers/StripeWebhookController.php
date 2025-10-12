<?php

namespace App\Http\Controllers;

use App\Models\PaymentInfo;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Stripe\Webhook as StripeWebhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('stripe.webhook_secret');

        try {
            if ($secret) {
                $event = StripeWebhook::constructEvent($payload, $sigHeader, $secret);
            } else {
                $event = json_decode($payload, false, 512, JSON_THROW_ON_ERROR);
            }
        } catch (\Throwable $e) {
            Log::warning('Stripe webhook signature failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $type = $secret ? $event->type : ($event->type ?? null);
        $object = $secret ? $event->data->object : ($event->data->object ?? null);

        if ($type === 'payment_intent.succeeded' && $object) {
            $transactionId = $object->id;
            $amount = (int) (($object->amount_received ?? $object->amount) ?? 0);
            $status = (string) ($object->status ?? 'succeeded');
            $paidAt = isset($object->created) ? Carbon::createFromTimestamp($object->created) : now();
            $metadata = (array) ($object->metadata ?? []);

            $info = PaymentInfo::updateOrCreate(
                ['transaction_id' => $transactionId],
                [
                    'provider' => 'stripe',
                    'amount' => $amount,
                    'status' => $status,
                    'paid_at' => $paidAt,
                ]
            );

            // Try to associate to an order if we can infer it
            if (! $info->order_id) {
                $userId = isset($metadata['user_id']) ? (int) $metadata['user_id'] : null;
                if ($userId) {
                    $order = Order::query()
                        ->where('user_id', $userId)
                        ->where('total_cents', $amount)
                        ->latest('id')
                        ->first();
                    if ($order) {
                        $info->order_id = $order->id;
                        $info->save();
                    }
                }
            }
        }

        return response()->json(['received' => true]);
    }
}

