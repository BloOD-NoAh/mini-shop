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
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class CartController extends Controller
{
    public function view(): Response|JsonResponse|InertiaResponse
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

        $cart = $items->map(fn ($i) => [
            'product' => [
                'id' => $i->product_id,
                'name' => $i->product->name,
                'slug' => $i->product->slug,
                'image_path' => $i->product->image_path,
                'price_cents' => (int) $i->product->price_cents,
                'price_formatted' => $i->product->price_formatted,
            ],
            'quantity' => (int) $i->quantity,
        ])->values();

        return Inertia::render('Cart/Index', [
            'items' => $cart,
            'subtotalCents' => $subtotal,
            'currency' => config('stripe.currency', 'usd'),
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
