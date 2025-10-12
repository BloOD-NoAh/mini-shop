<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Product #{{ $product->id }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="container-narrow">
            @if (session('status'))
                <div class="mb-4 p-4 rounded bg-green-100 text-green-800">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-4 rounded bg-red-100 text-red-800">
                    <ul class="list-disc ml-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ url('/admin/products/'.$product->id) }}" method="POST" enctype="multipart/form-data" class="card space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="input-label">Name</label>
                    <input name="name" value="{{ old('name', $product->name) }}" class="input-field" required>
                </div>
                <div>
                    <label class="input-label">Slug</label>
                    <input name="slug" value="{{ old('slug', $product->slug) }}" class="input-field" required>
                </div>
                <div>
                    <label class="input-label">Description</label>
                    <textarea name="description" class="input-field" rows="4">{{ old('description', $product->description) }}</textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="input-label">Price</label>
                        <input type="number" name="price_cents" value="{{ old('price_cents', number_format(((int) $product->price_cents)/100, 2, '.', '')) }}" min="0" step="0.01" class="input-field">
                    </div>
                    <div>
                        <label class="input-label">Stock</label>
                        <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" min="0" class="input-field" required>
                    </div>
                    <div>
                        <label class="input-label">Category</label>
                        <input name="category" value="{{ old('category', $product->category) }}" class="input-field">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="input-label">Net Price</label>
                        <input type="number" name="net_price_cents" value="{{ old('net_price_cents', $product->net_price_cents) }}" min="0" step="0.01" class="input-field">
                    </div>
                    <div>
                        <label class="input-label">Tax</label>
                        <input type="number" name="tax_cents" value="{{ old('tax_cents', $product->tax_cents) }}" min="0" step="0.01" class="input-field">
                    </div>
                    <div>
                        <label class="input-label">Selling Price</label>
                        <input type="number" name="selling_price_cents" value="{{ old('selling_price_cents', $product->selling_price_cents) }}" min="0" step="0.01" class="input-field">
                    </div>
                </div>
                <div>
                    <label class="input-label">Image</label>
                    <input type="file" name="image" accept="image/*" class="input-field">
                    @if ($product->image_path)
                        <div class="mt-2">
                            <img src="{{ asset($product->image_path) }}" alt="{{ $product->name }}" class="w-32 h-32 object-cover rounded">
                        </div>
                    @endif
                </div>
                <div class="flex items-center justify-end gap-2">
                    <a href="{{ url('/admin/products') }}" class="btn-muted">Cancel</a>
                    <button class="btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>




