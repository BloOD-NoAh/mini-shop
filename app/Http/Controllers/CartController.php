<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartRequest;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function view(): Response|JsonResponse
    {
        $userId = auth()->id();

        $items = CartItem::query()
            ->where('user_id', $userId)
            ->with('product')
            ->get();

        if (request()->wantsJson()) {
            return response()->json([
                'items' => $items,
            ]);
        }

        $subtotal = (int) $items->sum(fn ($i) => $i->product->price_cents * $i->quantity);

        return response()->view('cart.view', [
            'items' => $items,
            'subtotal_cents' => $subtotal,
        ]);
    }

    public function add(AddToCartRequest $request, Product $product): JsonResponse|RedirectResponse
    {
        $userId = auth()->id();
        $quantity = (int) $request->validated()['quantity'];

        $item = CartItem::query()
            ->firstOrNew([
                'user_id' => $userId,
                'product_id' => $product->id,
            ]);

        $item->quantity = max(1, (int) $item->quantity + $quantity);
        $item->save();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Added to cart',
                'item' => $item->load('product'),
            ], 201);
        }

        return redirect()->back()->with('status', 'Added to cart');
    }

    public function update(UpdateCartRequest $request, Product $product): JsonResponse|RedirectResponse
    {
        $userId = auth()->id();
        $quantity = (int) $request->validated()['quantity'];

        $item = CartItem::query()
            ->where('user_id', $userId)
            ->where('product_id', $product->id)
            ->first();

        if (! $item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        $item->quantity = max(1, $quantity);
        $item->save();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Cart updated',
                'item' => $item->load('product'),
            ]);
        }

        return redirect()->back()->with('status', 'Cart updated');
    }

    public function remove(Product $product): JsonResponse|RedirectResponse
    {
        $userId = auth()->id();

        CartItem::query()
            ->where('user_id', $userId)
            ->where('product_id', $product->id)
            ->delete();

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->back()->with('status', 'Item removed');
    }
}
