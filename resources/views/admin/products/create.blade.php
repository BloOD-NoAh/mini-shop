<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">New Product</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
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

            <form action="{{ url('/admin/products') }}" method="POST" enctype="multipart/form-data" class="bg-white shadow rounded p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input name="name" value="{{ old('name') }}" class="mt-1 w-full rounded border-gray-300" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Slug</label>
                    <input name="slug" value="{{ old('slug') }}" class="mt-1 w-full rounded border-gray-300" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" class="mt-1 w-full rounded border-gray-300" rows="4">{{ old('description') }}</textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Price (cents)</label>
                        <input type="number" name="price_cents" value="{{ old('price_cents') }}" min="0" class="mt-1 w-full rounded border-gray-300" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Stock</label>
                        <input type="number" name="stock" value="{{ old('stock') }}" min="0" class="mt-1 w-full rounded border-gray-300" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Category</label>
                        <input name="category" value="{{ old('category') }}" class="mt-1 w-full rounded border-gray-300">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Image</label>
                    <input type="file" name="image" accept="image/*" class="mt-1 w-full rounded border-gray-300">
                </div>
                <div class="flex items-center justify-end gap-2">
                    <a href="{{ url('/admin/products') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200">Cancel</a>
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Create</button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>
