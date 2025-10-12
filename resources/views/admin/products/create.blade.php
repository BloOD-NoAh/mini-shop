<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">New Product</h2>
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

            <form action="{{ url('/admin/products') }}" method="POST" enctype="multipart/form-data" class="card space-y-4">
                @csrf
                <div>
                    <label class="input-label">Name</label>
                    <input name="name" value="{{ old('name') }}" class="input-field" required>
                </div>
                <div>
                    <label class="input-label">Slug</label>
                    <input name="slug" value="{{ old('slug') }}" class="input-field" required>
                </div>
                <div>
                    <label class="input-label">Description</label>
                    <textarea name="description" class="input-field" rows="4">{{ old('description') }}</textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="input-label">Price</label>
                        <input type="number" name="price_cents" value="{{ old('price_cents') }}" min="0" class="input-field" step="0.01">
                    </div>
                    <div>
                        <label class="input-label">Stock</label>
                        <input type="number" name="stock" value="{{ old('stock') }}" min="0" class="input-field" required>
                    </div>
                    <div>
                        <label class="input-label">Category</label>
                        <input name="category" value="{{ old('category') }}" class="input-field">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="input-label">Net Price</label>
                        <input type="number" name="net_price_cents" value="{{ old('net_price_cents') }}" min="0" class="input-field" required step="0.01">
                    </div>
                    <div>
                        <label class="input-label">Tax</label>
                        <input type="number" name="tax_cents" value="{{ old('tax_cents') }}" min="0" class="input-field" required step="0.01">
                    </div>
                    <div>
                        <label class="input-label">Selling Price</label>
                        <input type="number" name="selling_price_cents" value="{{ old('selling_price_cents') }}" min="0" class="input-field" required step="0.01">
                    </div>
                </div>
                <div>
                    <label class="input-label">Image</label>
                    <input type="file" name="image" accept="image/*" class="input-field">
                </div>
                <div class="flex items-center justify-end gap-2">
                    <a href="{{ url('/admin/products') }}" class="btn-muted">Cancel</a>
                    <button class="btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>


