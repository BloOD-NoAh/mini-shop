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

        // Keep legacy integer price_cents aligned from selling price (decimal)
        if (isset($data['selling_price_cents']) && $data['selling_price_cents'] !== null && $data['selling_price_cents'] !== '') {
            $data['price_cents'] = (int) round(((float) $data['selling_price_cents']) * 100);
        } elseif (isset($data['price_cents']) && $data['price_cents'] !== null && $data['price_cents'] !== '') {
            // If raw price_cents provided as decimal (backward compatibility), cast to int cents
            $data['price_cents'] = (int) round(((float) $data['price_cents']) * 100);
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['image_path'] = 'storage/'.$path;
        }

        $product = Product::create($data);

        // Create variants if provided via JSON payload
        $variantsJson = $request->input('variants_json');
        if ($variantsJson) {
            try {
                $variants = json_decode($variantsJson, true, 512, JSON_THROW_ON_ERROR);
                if (is_array($variants)) {
                    foreach ($variants as $v) {
                        if (!is_array($v)) continue;
                        $attrs = isset($v['attributes']) && is_array($v['attributes']) ? $v['attributes'] : [];
                        $netDec = isset($v['net_price']) ? (float) $v['net_price'] : null;
                        $taxDec = isset($v['tax']) ? (float) $v['tax'] : null;
                        $priceDec = isset($v['price']) ? (float) $v['price'] : (isset($netDec) || isset($taxDec) ? (float) (($netDec ?? 0) + ($taxDec ?? 0)) : null);
                        $stock = isset($v['stock']) ? (int) $v['stock'] : 0;
                        $sku = isset($v['sku']) ? (string) $v['sku'] : null;
                        $product->variants()->create([
                            'sku' => $sku,
                            'attributes' => $attrs,
                            // legacy cents for compatibility
                            'price_cents' => isset($priceDec) ? (int) round($priceDec * 100) : null,
                            'net_price_cents' => isset($netDec) ? (int) round($netDec * 100) : null,
                            'tax_cents' => isset($taxDec) ? (int) round($taxDec * 100) : null,
                            'selling_price_cents' => isset($priceDec) ? (int) round($priceDec * 100) : null,
                            // new decimal columns
                            'price' => $priceDec,
                            'net_price' => $netDec,
                            'tax' => $taxDec,
                            'stock' => $stock,
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                // Ignore variant import errors silently for now
            }
        }
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Created', 'product' => $product], 201);
        }
        return redirect('/admin/products')->with('status', 'Product created');
    }

    public function edit(Product $product)
    {
        if (request()->wantsJson()) {
            $product->load('variants');
            return response()->json($product);
        }
        return view('admin.products.edit', [
            'product' => $product,
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();

        // Keep legacy integer price_cents aligned from selling price (decimal)
        if (isset($data['selling_price_cents']) && $data['selling_price_cents'] !== null && $data['selling_price_cents'] !== '') {
            $data['price_cents'] = (int) round(((float) $data['selling_price_cents']) * 100);
        } elseif (isset($data['price_cents']) && $data['price_cents'] !== null && $data['price_cents'] !== '') {
            $data['price_cents'] = (int) round(((float) $data['price_cents']) * 100);
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['image_path'] = 'storage/'.$path;
        }
        $product->update($data);

        // Replace variants if provided
        $variantsJson = $request->input('variants_json');
        if ($variantsJson !== null) {
            try {
                $variants = json_decode($variantsJson, true, 512, JSON_THROW_ON_ERROR);
                // Wipe and recreate
                $product->variants()->delete();
                if (is_array($variants)) {
                    foreach ($variants as $v) {
                        if (!is_array($v)) continue;
                        $attrs = isset($v['attributes']) && is_array($v['attributes']) ? $v['attributes'] : [];
                        $netDec = isset($v['net_price']) ? (float) $v['net_price'] : null;
                        $taxDec = isset($v['tax']) ? (float) $v['tax'] : null;
                        $priceDec = isset($v['price']) ? (float) $v['price'] : (isset($netDec) || isset($taxDec) ? (float) (($netDec ?? 0) + ($taxDec ?? 0)) : null);
                        $stock = isset($v['stock']) ? (int) $v['stock'] : 0;
                        $sku = isset($v['sku']) ? (string) $v['sku'] : null;
                        $product->variants()->create([
                            'sku' => $sku,
                            'attributes' => $attrs,
                            'price_cents' => isset($priceDec) ? (int) round($priceDec * 100) : null,
                            'net_price_cents' => isset($netDec) ? (int) round($netDec * 100) : null,
                            'tax_cents' => isset($taxDec) ? (int) round($taxDec * 100) : null,
                            'selling_price_cents' => isset($priceDec) ? (int) round($priceDec * 100) : null,
                            'price' => $priceDec,
                            'net_price' => $netDec,
                            'tax' => $taxDec,
                            'stock' => $stock,
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                // Ignore variant import errors silently
            }
        }
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
