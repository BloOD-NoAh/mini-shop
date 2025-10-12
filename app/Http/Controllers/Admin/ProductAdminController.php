<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductAdminController extends Controller
{
    public function index()
    {
        $products = Product::query()->latest('id')->paginate(20);

        if (request()->wantsJson()) {
            return response()->json($products);
        }

        return view('admin.products.index', [
            'products' => $products,
        ]);
    }

    public function create()
    {
        return view('admin.products.create');
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        // Convert storefront price to cents (from decimal inputs)
        if (isset($data['selling_price_cents'])) {
            $data['price_cents'] = (int) round(((float) $data['selling_price_cents']) * 100);
        } elseif (isset($data['price_cents'])) {
            $data['price_cents'] = (int) round(((float) $data['price_cents']) * 100);
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['image_path'] = 'storage/'.$path;
        }

        $product = Product::create($data);
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Created', 'product' => $product], 201);
        }
        return redirect('/admin/products')->with('status', 'Product created');
    }

    public function edit(Product $product)
    {
        if (request()->wantsJson()) {
            return response()->json($product);
        }
        return view('admin.products.edit', [
            'product' => $product,
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();

        if (isset($data['selling_price_cents'])) {
            $data['price_cents'] = (int) round(((float) $data['selling_price_cents']) * 100);
        } elseif (isset($data['price_cents'])) {
            $data['price_cents'] = (int) round(((float) $data['price_cents']) * 100);
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['image_path'] = 'storage/'.$path;
        }
        $product->update($data);
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Updated', 'product' => $product]);
        }
        return redirect('/admin/products')->with('status', 'Product updated');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        if (request()->wantsJson()) {
            return response()->json(null, 204);
        }
        return redirect('/admin/products')->with('status', 'Product deleted');
    }
}
