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

                @if ($order->shipping_address)
                    <div class="mt-2 p-3 rounded bg-gray-50">
                        <div class="text-gray-700 mb-1">Shipping Address</div>
                        <div class="text-gray-900">
                            {{ $order->shipping_address['full_name'] ?? '' }}<br>
                            {{ $order->shipping_address['line1'] ?? '' }}<br>
                            @if (!empty($order->shipping_address['line2']))
                                {{ $order->shipping_address['line2'] }}<br>
                            @endif
                            {{ $order->shipping_address['city'] ?? '' }}@if(!empty($order->shipping_address['state'])), {{ $order->shipping_address['state'] }}@endif {{ $order->shipping_address['postal_code'] ?? '' }}<br>
                            {{ $order->shipping_address['country'] ?? '' }}
                            @if (!empty($order->shipping_address['phone']))
                                <br>Phone: {{ $order->shipping_address['phone'] }}
                            @endif
                        </div>
                    </div>
                @endif

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
