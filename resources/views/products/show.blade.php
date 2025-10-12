<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $product->name }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="container-narrow">
            @if (session('status'))
                <div class="mb-4 p-4 rounded bg-green-100 text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="card p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    @if ($product->image_path)
                        <img src="{{ asset($product->image_path) }}" alt="{{ $product->name }}" class="w-full h-80 object-cover rounded">
                    @else
                        <div class="w-full h-80 bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400 rounded">No Image</div>
                    @endif
                </div>
                <div class="space-y-4">
                    <h1 class="text-2xl font-semibold">{{ $product->name }}</h1>
                    <p class="text-lg">{{ $product->price_formatted }}</p>
                    <p class="text-gray-700 dark:text-gray-300">{{ $product->description }}</p>

                    <form action="{{ url('/cart/add/'.$product->id) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="input-label">Quantity</label>
                            <input type="number" name="quantity" value="1" min="1" class="w-24 input-field">
                        </div>
                        <button type="submit" class="btn-primary">Add to Cart</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

