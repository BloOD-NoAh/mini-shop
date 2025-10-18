<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Customers</h2>
    </x-slot>

    <div class="py-6">
        <div class="container-wide">
            <div class="card p-6 space-y-4">
                <form method="GET" action="{{ route('admin.customers') }}" class="flex items-center gap-2">
                    <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Search name or email..." class="input-field w-64" />
                    <button class="btn-muted">Search</button>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="thead">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-600 dark:text-gray-300">ID</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-600 dark:text-gray-300">Name</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-600 dark:text-gray-300">Email</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-600 dark:text-gray-300">Orders</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-600 dark:text-gray-300">Total Spent</th>
                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-600 dark:text-gray-300">Joined</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="tbody">
                            @forelse ($customers as $c)
                                <tr>
                                    <td class="px-4 py-2">#{{ $c->id }}</td>
                                    <td class="px-4 py-2">{{ $c->name }}</td>
                                    <td class="px-4 py-2">{{ $c->email }}</td>
                                    <td class="px-4 py-2">{{ $c->orders_count }}</td>
                                    <td class="px-4 py-2">$ {{ number_format(((int) ($c->orders_sum_total_cents ?? 0)) / 100, 2) }}</td>
                                    <td class="px-4 py-2 text-gray-600 dark:text-gray-400">{{ $c->created_at?->format('Y-m-d') }}</td>
                                    <td class="px-4 py-2 text-right">
                                        <a href="{{ route('admin.customers.show', $c) }}" class="btn-muted">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-4 text-center text-gray-600 dark:text-gray-400">No customers found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex items-center justify-between">
                    <div>
                        @if (method_exists($customers, 'links'))
                            {{ $customers->links() }}
                        @endif
                    </div>
                    <div>
                        <a href="{{ route('admin.customers.export', request()->only('q')) }}" class="btn-muted">Export CSV</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>


