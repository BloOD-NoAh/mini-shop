<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Products
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-4 rounded bg-green-100 text-green-800">
                    {{ session('status') }}
                </div>
            @endif
            <div class="mb-6">
                <form action="{{ url('/search') }}" method="GET" class="flex items-center gap-2">
                    <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Search products..." class="w-full rounded border-gray-300" />
                    <button class="px-4 py-2 bg-gray-800 text-white rounded">Search</button>
                </form>
                @isset($categories)
                    <div class="mt-3 flex flex-wrap gap-2">
                        <a href="{{ url('/') }}" class="px-3 py-1 rounded-full text-sm border {{ empty($activeCategory) ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' }}">All</a>
                        @foreach ($categories as $cat)
                            <a href="{{ url('/category/'.$cat) }}" class="px-3 py-1 rounded-full text-sm border {{ ($activeCategory ?? '') === $cat ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' }}">{{ $cat }}</a>
                        @endforeach
                    </div>
                @endisset
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @forelse ($products as $product)
                    <div class="bg-white overflow-hidden shadow rounded">
                        @if ($product->image_path)
                            <img src="{{ asset($product->image_path) }}" alt="{{ $product->name }}" class="w-full h-40 object-cover">
                        @else
                            <div class="w-full h-40 bg-gray-100 flex items-center justify-center text-gray-400">No Image</div>
                        @endif
                        <div class="p-4 space-y-2">
                            <a href="{{ url('/products/'.$product->slug) }}" class="block font-semibold text-gray-900 hover:text-indigo-600">
                                {{ $product->name }}
                            </a>
                            @if (!empty($product->category))
                                <div class="text-xs inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-700">{{ $product->category }}</div>
                            @endif
                            <p class="text-gray-700">$ {{ number_format($product->price_cents / 100, 2) }}</p>
                            <form action="{{ url('/cart/add/'.$product->id) }}" method="POST" class="flex items-center gap-2">
                                @csrf
                                <input type="number" name="quantity" value="1" min="1" class="w-20 rounded border-gray-300">
                                <button type="submit" class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white text-sm font-medium rounded hover:bg-indigo-700">Add to Cart</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-600">No products found.</p>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
