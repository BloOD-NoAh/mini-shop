<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Customer #{{ $customer->id }} — {{ $customer->name }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="container-wide space-y-6">
            <div class="card p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <div class="text-sm text-gray-500">Name</div>
                    <div class="font-medium">{{ $customer->name }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Email</div>
                    <div class="font-medium">{{ $customer->email }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Joined</div>
                    <div class="font-medium">{{ $customer->created_at?->format('Y-m-d') }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Orders</div>
                    <div class="font-medium">{{ $totals['orders_count'] }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500">Total Spent</div>
                    <div class="font-medium">$ {{ number_format(((int) $totals['orders_sum_total_cents'])/100, 2) }}</div>
                </div>
                <div class="flex items-end justify-end gap-2">
                    <a href="{{ route('admin.customers.exportOrders', $customer) }}" class="btn-muted">Export Orders</a>
                </div>
            </div>

            <div class="card p-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold">Addresses</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse ($customer->addresses as $a)
                        <div class="p-4 rounded border {{ $a->is_default ? 'border-primary-600' : 'border-gray-200 dark:border-gray-800' }}">
                            <div class="font-medium">{{ $a->full_name }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">{{ $a->line1 }} {{ $a->line2 }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">{{ $a->city }}, {{ $a->state }} {{ $a->postal_code }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">{{ $a->country }}</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">{{ $a->phone }}</div>
                            @if($a->is_default)
                                <div class="mt-2 badge-success inline-block">Default</div>
                            @endif
                        </div>
                    @empty
                        <div class="text-gray-600 dark:text-gray-400">No addresses found.</div>
                    @endforelse
                </div>
            </div>

            <div class="card p-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold">Orders</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="thead">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider">ID</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider">Items</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider">Total</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider">Date</th>
                            </tr>
                        </thead>
                        <tbody class="tbody">
                            @forelse ($orders as $o)
                                <tr>
                                    <td class="px-4 py-2"><a href="{{ route('admin.orders.show', $o) }}" class="text-indigo-600 hover:underline">#{{ $o->id }}</a></td>
                                    <td class="px-4 py-2">{{ ucfirst($o->status) }}</td>
                                    <td class="px-4 py-2">{{ $o->items_count }}</td>
                                    <td class="px-4 py-2">$ {{ number_format(((int) $o->total_cents)/100, 2) }}</td>
                                    <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ $o->created_at?->format('Y-m-d H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-4 text-center text-gray-600 dark:text-gray-400">No orders yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $orders->links() }}</div>
            </div>
        </div>
    </div>
</x-admin-layout>
