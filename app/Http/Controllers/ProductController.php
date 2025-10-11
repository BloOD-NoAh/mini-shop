<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q')) ?: null;
        $category = $request->input('category');

        $query = Product::query();
        if ($q) {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%$q%")
                    ->orWhere('description', 'like', "%$q%");
            });
        }
        if ($category) {
            $query->where('category', $category);
        }

        $products = $query->latest('id')->paginate(12)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($products);
        }

        $categories = Product::query()
            ->select('category')
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('products.index', [
            'products' => $products,
            'categories' => $categories,
            'activeCategory' => $category,
            'q' => $q,
        ]);
    }

    public function show(Product $product)
    {
        if (request()->wantsJson()) {
            return response()->json($product);
        }

        return view('products.show', [
            'product' => $product,
        ]);
    }

    public function search(Request $request)
    {
        return $this->index($request);
    }

    public function category(Request $request, string $name)
    {
        $request->merge(['category' => $name]);
        return $this->index($request);
    }
}
