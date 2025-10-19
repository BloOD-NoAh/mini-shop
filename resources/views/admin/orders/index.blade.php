<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Orders</h2>
    </x-slot>

    <div class="py-6">
        <div class="container-wide space-y-4">
            @if (session('status'))
                <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-3 rounded bg-red-100 text-red-800">{{ session('error') }}</div>
            @endif

            <form method="GET" action="{{ route('admin.orders.index') }}" class="card p-4 grid grid-cols-1 md:grid-cols-6 gap-3">
                <div class="md:col-span-2">
                    <label class="input-label">Search</label>
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Order ID, name, email" class="input-field" />
                </div>
                <div>
                    <label class="input-label">Status</label>
                    <select name="status" class="input-field">
                        <option value="">All</option>
                        @foreach ($statuses as $s)
                            <option value="{{ $s }}" {{ ($filters['status'] ?? '') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="input-label">From</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="input-field" />
                </div>
                <div>
                    <label class="input-label">To</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="input-field" />
                </div>
                <div class="flex items-end">
                    <button class="btn-muted">Filter</button>
                </div>
                <div class="flex items-end justify-end">
                    <a href="{{ route('admin.orders.export', request()->only('q','status','date_from','date_to')) }}" class="btn-muted">Export CSV</a>
                </div>
            </form>

            <div class="card p-0 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="thead">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider">ID</th>
                            <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider">Customer</th>
                            <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                            <th class="px-4 py-2 text-right text-xs font-medium uppercase tracking-wider">Items</th>
                            <th class="px-4 py-2 text-right text-xs font-medium uppercase tracking-wider">Total</th>
                            <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider">Date</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="tbody">
                        @forelse ($orders as $o)
                            <tr>
                                <td class="px-4 py-2"><a href="{{ route('admin.orders.show', $o) }}" class="text-indigo-600 hover:underline">#{{ $o->id }}</a></td>
                                <td class="px-4 py-2">
                                    <a href="{{ route('admin.customers.show', $o->user) }}" class="hover:underline">{{ $o->user?->name }}</a>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">{{ $o->user?->email }}</div>
                                </td>
                                <td class="px-4 py-2">
                                    <form method="POST" action="{{ route('admin.orders.update', $o) }}" class="flex items-center gap-2">
                                        @csrf
                                        @method('PUT')
                                        <select name="status" class="input-field">
                                            @foreach ($statuses as $s)
                                                <option value="{{ $s }}" {{ $o->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn-muted">Update</button>
                                    </form>
                                </td>
                                <td class="px-4 py-2 text-right">{{ $o->items_count }}</td>
                                <td class="px-4 py-2 text-right">$ {{ number_format(((int) $o->total_cents)/100, 2) }}</td>
                                <td class="px-4 py-2 text-left text-gray-600 dark:text-gray-400">{{ $o->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-2 text-right"><a href="{{ route('admin.orders.show', $o) }}" class="btn-muted">View</a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-4 text-center text-gray-600 dark:text-gray-400">No orders found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>
