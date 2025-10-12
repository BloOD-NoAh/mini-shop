<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Order #{{ $order->id }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-4 rounded bg-green-100 text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow rounded p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-gray-700">Status</div>
                        <div class="font-medium text-gray-900">{{ ucfirst($order->status) }}</div>
                    </div>
                    <div class="text-lg font-semibold">Total: {{ $order->total_formatted }}</div>
                </div>

                <div class="divide-y">
                    @foreach ($order->items as $item)
                        <div class="py-3 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                @if ($item->product?->image_path)
                                    <img src="{{ asset($item->product->image_path) }}" alt="{{ $item->product->name }}" class="w-12 h-12 object-cover rounded">
                                @endif
                                <div>
                                    <div class="font-medium text-gray-900">{{ $item->product?->name ?? 'Product' }}</div>
                                    <div class="text-gray-700">Qty: {{ $item->quantity }}</div>
                                </div>
                            </div>
                            <div class="text-gray-900">{{ $item->unit_price_formatted }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
