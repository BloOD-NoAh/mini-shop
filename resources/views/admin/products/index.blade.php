<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin • Products</h2>
            <a href="{{ url('/admin/products/create') }}" class="px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">New Product</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-4 rounded bg-green-100 text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow rounded overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($products as $product)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-700">{{ $product->id }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ $product->name }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900">$ {{ number_format($product->price_cents / 100, 2) }}</td>
                                <td class="px-4 py-2 text-sm text-gray-900">{{ $product->stock }}</td>
                                <td class="px-4 py-2 text-right space-x-2">
                                    <a href="{{ url('/admin/products/'.$product->id.'/edit') }}" class="px-3 py-1 bg-gray-800 text-white text-sm rounded hover:bg-gray-900">Edit</a>
                                    <form action="{{ url('/admin/products/'.$product->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700" onclick="return confirm('Delete this product?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $products->links() }}</div>
        </div>
    </div>
</x-app-layout>

