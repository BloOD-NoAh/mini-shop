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
            ->with(['product', 'variant'])
            ->get();

        if (request()->wantsJson()) {
            return response()->json([
                'items' => $items,
            ]);
        }

        $subtotal = (int) $items->sum(function ($i) {
            // Prefer variant decimal pricing
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

        $cart = $items->map(function ($i) {
            $variantAmount = $i->variant?->price
                ?? ($i->variant ? (($i->variant->net_price ?? 0) + ($i->variant->tax ?? 0)) : null);
            $unitPrice = $variantAmount !== null
                ? (int) round($variantAmount * 100)
                : (int) (
                    $i->variant?->selling_price_cents
                    ?? $i->variant?->price_cents
                    ?? ($i->product->selling_price_cents !== null ? (int) round(((float) $i->product->selling_price_cents) * 100) : $i->product->price_cents)
                );
            return [
                'id' => $i->id,
                'product' => [
                    'id' => $i->product_id,
                    'name' => $i->product->name,
                    'slug' => $i->product->slug,
                    'image_path' => $i->product->image_path,
                ],
                'variant' => $i->variant ? [
                    'id' => $i->product_variant_id,
                    'attributes' => $i->variant->attributes,
                    'price_cents' => (int) $unitPrice,
                ] : null,
                'unit_price_cents' => $unitPrice,
                'quantity' => (int) $i->quantity,
            ];
        })->values();

        return Inertia::render('Cart/Index', [
            'items' => $cart,
            'subtotalCents' => $subtotal,
            'currency' => config('stripe.currency', 'usd'),
        ]);
    }

    public function add(AddToCartRequest $request, Product $product): JsonResponse|RedirectResponse
    {
        $userId = auth()->id();
        $data = $request->validated();
        $quantity = (int) $data['quantity'];

        $variantId = $request->input('product_variant_id');
        if ($product->variants()->exists()) {
            if (!$variantId) {
                return redirect()->back()->withErrors(['variant' => 'Please select a variant']);
            }
            $variant = $product->variants()->where('id', $variantId)->first();
            if (!$variant) {
                return redirect()->back()->withErrors(['variant' => 'Invalid variant']);
            }
        } else {
            $variant = null;
        }

        $item = CartItem::query()->firstOrNew([
            'user_id' => $userId,
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
        ]);

        $item->quantity = max(1, (int) $item->quantity + $quantity);
        $item->save();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Added to cart',
                'item' => $item->load(['product','variant']),
            ], 201);
        }

        return redirect('/')->with('status', 'Added to cart');
    }

    public function update(UpdateCartRequest $request, Product $product): JsonResponse|RedirectResponse
    {
        $userId = auth()->id();
        $quantity = (int) $request->validated()['quantity'];

        $variantId = $request->input('product_variant_id');
        $query = CartItem::query()
            ->where('user_id', $userId)
            ->where('product_id', $product->id);
        if ($variantId !== null && $variantId !== '') {
            $query->where('product_variant_id', $variantId);
        }
        $item = $query->first();

        if (! $item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        $item->quantity = max(1, $quantity);
        $item->save();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Cart updated',
                'item' => $item->load(['product','variant']),
            ]);
        }

        return redirect()->back()->with('status', 'Cart updated');
    }

    public function remove(Product $product): JsonResponse|RedirectResponse
    {
        $userId = auth()->id();

        $variantId = request()->input('product_variant_id');
        $q = CartItem::query()
            ->where('user_id', $userId)
            ->where('product_id', $product->id);
        if ($variantId !== null && $variantId !== '') {
            $q->where('product_variant_id', $variantId);
        }
        $q->delete();

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->back()->with('status', 'Item removed');
    }

    public function updateItem(UpdateCartRequest $request, CartItem $item): JsonResponse|RedirectResponse
    {
        if ($item->user_id !== auth()->id()) {
            abort(403);
        }
        $item->quantity = max(1, (int) $request->validated()['quantity']);
        $item->save();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Cart updated',
                'item' => $item->load(['product','variant']),
            ]);
        }

        return redirect()->back()->with('status', 'Cart updated');
    }

    public function removeItem(CartItem $item): JsonResponse|RedirectResponse
    {
        if ($item->user_id !== auth()->id()) {
            abort(403);
        }
        $item->delete();

        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }

        return redirect()->back()->with('status', 'Item removed');
    }
}
