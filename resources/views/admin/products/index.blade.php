<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin • Products</h2>
            <a href="{{ url('/admin/products/create') }}" class="btn-primary">New Product</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="container-wide">
            @if (session('status'))
                <div class="mb-4 p-4 rounded bg-green-100 text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="card overflow-x-auto">
                <table class="table">
                    <thead class="thead">
                        <tr>
                            <th class="th">ID</th>
                            <th class="th">Name</th>
                            <th class="th">Price</th>
                            <th class="th">Net</th>
                            <th class="th">Tax</th>
                            <th class="th">Selling</th>
                            <th class="th">Stock</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="tbody">
                        @foreach ($products as $product)
                            <tr>
                                <td class="td">{{ $product->id }}</td>
                                <td class="td">{{ $product->name }}</td>
                                <td class="td">{{ money($product->price_cents) }}</td>
                                <td class="td">{{ money($product->net_price_cents, null, false) }}</td>
                                <td class="td">{{ money($product->tax_cents, null, false) }}</td>
                                <td class="td">{{ money($product->selling_price_cents, null, false) }}</td>
                                <td class="td">{{ $product->stock }}</td>
                                <td class="px-4 py-2 text-right space-x-2">
                                    <a href="{{ url('/admin/products/'.$product->id.'/edit') }}" class="btn-muted">Edit</a>
                                    <form action="{{ url('/admin/products/'.$product->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-danger" onclick="return confirm('Delete this product?')">Delete</button>
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
</x-admin-layout>





