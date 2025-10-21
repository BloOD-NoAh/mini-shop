<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\AiChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function assist(Request $request, AiChatService $ai)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'context' => 'required|string|in:product,order',
            'id' => 'required|integer',
        ]);

        $contextType = $validated['context'];
        $id = (int) $validated['id'];

        if ($contextType === 'product') {
            $product = Product::query()->with('variants')->findOrFail($id);
            $contextBlob = [
                'type' => 'product',
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price_formatted ?? null,
                    'category' => $product->category,
                    'variants' => $product->variants?->map(function ($v) {
                        return [
                            'id' => $v->id,
                            'sku' => $v->sku,
                            'attributes' => $v->attributes,
                            'price' => $v->price ?? null,
                            'stock' => $v->stock ?? null,
                        ];
                    })->all(),
                ],
            ];
            $system = $this->systemPrompt();
            $messages = [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => "Here are the product details in JSON. Only answer based on these.\n\n".json_encode($contextBlob, JSON_PRETTY_PRINT)],
                ['role' => 'user', 'content' => $validated['message']],
            ];
        } else { // order
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            $order = Order::query()->with(['items.product'])
                ->where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();
            $contextBlob = [
                'type' => 'order',
                'order' => [
                    'id' => $order->id,
                    'status' => $order->status,
                    'total' => $order->total_formatted ?? null,
                    'items' => $order->items->map(function ($i) {
                        return [
                            'id' => $i->id,
                            'product' => $i->product?->name,
                            'quantity' => $i->quantity,
                            'unit_price' => $i->unit_price_formatted ?? null,
                        ];
                    })->all(),
                    'shipping_address' => $order->shipping_address ?? null,
                ],
            ];
            $system = $this->systemPrompt();
            $messages = [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => "Here are the user's own order details in JSON. Only answer based on these.\n\n".json_encode($contextBlob, JSON_PRETTY_PRINT)],
                ['role' => 'user', 'content' => $validated['message']],
            ];
        }

        $answer = $ai->respond($messages);

        return response()->json([
            'reply' => $answer,
            'provider' => $ai->provider(),
        ]);
    }

    protected function systemPrompt(): string
    {
        return trim(<<<TXT
You are a concise, friendly customer support assistant for an online mini-shop in Malaysia. Strictly follow these rules:
- Only answer questions about the provided product details or the authenticated customer's own order details.
- If asked anything outside these, reply: "Sorry, I can only help with product or order details." and stop.
- Do NOT invent information. If the data is missing, say you don't have that detail.
- Keep answers short (1-3 sentences) and helpful.
TXT);
    }
}
