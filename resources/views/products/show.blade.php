<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $product->name }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-4 rounded bg-green-100 text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow rounded p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    @if ($product->image_path)
                        <img src="{{ asset($product->image_path) }}" alt="{{ $product->name }}" class="w-full h-80 object-cover rounded">
                    @else
                        <div class="w-full h-80 bg-gray-100 flex items-center justify-center text-gray-400 rounded">No Image</div>
                    @endif
                </div>
                <div class="space-y-4">
                    <h1 class="text-2xl font-semibold text-gray-900">{{ $product->name }}</h1>
                    <p class="text-lg text-gray-800">$ {{ number_format($product->price_cents / 100, 2) }}</p>
                    <p class="text-gray-700">{{ $product->description }}</p>

                    <form action="{{ url('/cart/add/'.$product->id) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Quantity</label>
                            <input type="number" name="quantity" value="1" min="1" class="mt-1 w-24 rounded border-gray-300">
                        </div>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-medium rounded hover:bg-indigo-700">Add to Cart</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

