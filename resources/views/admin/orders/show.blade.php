<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Order #{{ $order->id }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="container-wide space-y-6">
            <div class="card p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <div class="text-sm text-gray-500">Customer</div>
                    <div class="font-medium">
                        <a href="{{ route('admin.customers.show', $order->user) }}" class="text-indigo-600 hover:underline">
                            #{{ $order->user?->id }} — {{ $order->user?->name }} ({{ $order->user?->email }})
                        </a>
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Status</div>
                    <div class="font-medium capitalize">{{ $order->status }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Placed At</div>
                    <div class="font-medium">{{ $order->created_at?->format('Y-m-d H:i') }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Total</div>
                    <div class="font-medium">{{ $order->total_formatted }}</div>
                </div>
                <div class="flex items-end justify-end gap-2">
                    <form method="POST" action="{{ route('admin.orders.refund', $order) }}" class="flex items-center gap-2">
                        @csrf
                        <input type="number" name="amount" step="0.01" min="0" class="input-field w-32" placeholder="Full" />
                        <button class="btn-danger">Refund</button>
                    </form>
                </div>
                <div class="md:col-span-2">
                    <div class="text-sm text-gray-500">Shipping Address</div>
                    @php($a = $order->shipping_address ?? [])
                    <div class="font-medium">{{ $a['full_name'] ?? '' }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ $a['line1'] ?? '' }} {{ $a['line2'] ?? '' }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ $a['city'] ?? '' }}, {{ $a['state'] ?? '' }} {{ $a['postal_code'] ?? '' }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ $a['country'] ?? '' }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ $a['phone'] ?? '' }}</div>
                </div>
            </div>

            <div class="card p-6">
                <h3 class="font-semibold mb-3">Items</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="thead">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider">Product</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider">Variant</th>
                                <th class="px-4 py-2 text-right text-xs font-medium uppercase tracking-wider">Unit</th>
                                <th class="px-4 py-2 text-right text-xs font-medium uppercase tracking-wider">Qty</th>
                                <th class="px-4 py-2 text-right text-xs font-medium uppercase tracking-wider">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="tbody">
                            @foreach ($order->items as $it)
                                @php($unit = (int) $it->unit_price_cents)
                                @php($subtotal = $unit * (int) $it->quantity)
                                <tr>
                                    <td class="px-4 py-2">
                                        {{ $it->product?->name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">
                                        @if($it->variant && is_array($it->variant->attributes))
                                            @foreach($it->variant->attributes as $k => $v)
                                                <span class="mr-2">{{ $k }}: {{ $v }}</span>
                                            @endforeach
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-right">$ {{ number_format($unit/100, 2) }}</td>
                                    <td class="px-4 py-2 text-right">{{ (int) $it->quantity }}</td>
                                    <td class="px-4 py-2 text-right">$ {{ number_format($subtotal/100, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if($order->paymentInfo)
                <div class="card p-6">
                    <h3 class="font-semibold mb-3">Payment</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <div class="text-gray-500">Provider</div>
                            <div class="font-medium">{{ $order->paymentInfo->provider }}</div>
                        </div>
                        <div>
                            <div class="text-gray-500">Amount</div>
                            <div class="font-medium">$ {{ number_format(((int) $order->paymentInfo->amount)/100, 2) }}</div>
                        </div>
                        <div>
                            <div class="text-gray-500">Status</div>
                            <div class="font-medium capitalize">{{ $order->paymentInfo->status }}</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
