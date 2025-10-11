<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Your Cart
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-4 rounded bg-green-100 text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow rounded p-4">
                @if ($items->isEmpty())
                    <p class="text-gray-600">Your cart is empty.</p>
                @else
                    <div class="divide-y">
                        @foreach ($items as $item)
                            <div class="py-4 flex items-center justify-between gap-4">
                                <div class="flex items-center gap-4">
                                    @if ($item->product->image_path)
                                        <img src="{{ asset($item->product->image_path) }}" alt="{{ $item->product->name }}" class="w-16 h-16 object-cover rounded">
                                    @else
                                        <div class="w-16 h-16 bg-gray-100 flex items-center justify-center text-gray-400 rounded">—</div>
                                    @endif
                                    <div>
                                        <a href="{{ url('/products/'.$item->product->slug) }}" class="font-medium text-gray-900 hover:text-indigo-600">{{ $item->product->name }}</a>
                                        <div class="text-gray-700">$ {{ number_format($item->product->price_cents / 100, 2) }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <form action="{{ url('/cart/update/'.$item->product_id) }}" method="POST" class="flex items-center gap-2">
                                        @csrf
                                        <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" class="w-20 rounded border-gray-300">
                                        <button class="px-3 py-2 bg-gray-800 text-white text-sm rounded hover:bg-gray-900">Update</button>
                                    </form>
                                    <form action="{{ url('/cart/remove/'.$item->product_id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button class="px-3 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700">Remove</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 flex items-center justify-between">
                        <div class="text-lg font-semibold">Subtotal: $ {{ number_format($subtotal_cents / 100, 2) }}</div>
                        <a href="{{ url('/checkout') }}" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Checkout</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
