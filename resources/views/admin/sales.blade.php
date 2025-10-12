<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Sales</h2>
    </x-slot>

    <div class="py-6">
        <div class="container-wide">
            <div class="mb-4 card p-4">
                <form method="GET" action="{{ route('admin.sales') }}" class="grid grid-cols-1 md:grid-cols-7 gap-3 items-end">
                    <div>
                        <label class="input-label">Status</label>
                        <select name="status" class="input-field">
                            <option value="all" {{ (($filters['status'] ?? 'succeeded') === 'all') ? 'selected' : '' }}>All</option>
                            @foreach ($allStatuses as $s)
                                <option value="{{ $s }}" {{ (($filters['status'] ?? 'succeeded') === $s) ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="input-label">Group By</label>
                        <select name="group" class="input-field">
                            @php($g = $filters['group'] ?? 'day')
                            <option value="day" {{ $g === 'day' ? 'selected' : '' }}>Day</option>
                            <option value="week" {{ $g === 'week' ? 'selected' : '' }}>Week</option>
                            <option value="month" {{ $g === 'month' ? 'selected' : '' }}>Month</option>
                        </select>
                    </div>
                    <div>
                        <label class="input-label">From (paid_at)</label>
                        <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="input-field" />
                    </div>
                    <div>
                        <label class="input-label">To (paid_at)</label>
                        <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="input-field" />
                    </div>
                    <div>
                        <label class="input-label">Options</label>
                        <label class="inline-flex items-center gap-2 mt-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="checkbox" name="stack" value="1" {{ ($filters['stack'] ?? false) ? 'checked' : '' }}>
                            <span>Stack by status</span>
                        </label>
                    </div>
                    <div class="md:col-span-2 flex gap-2">
                        <button class="btn-muted" type="submit">Apply Filters</button>
                        <a href="{{ route('admin.sales') }}" class="btn-muted">Reset</a>
                        <a href="{{ route('admin.sales.export', request()->query()) }}" class="btn-primary">Export CSV</a>
                    </div>
                </form>
            </div>
            <div class="card p-6 mb-4">
                <canvas id="salesChart" height="120"></canvas>
            </div>
            <div class="card p-4 mb-4 flex items-center gap-6">
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total Revenue</div>
                    <div class="text-xl font-semibold">{{ number_format(((int) ($totals->revenue_cents ?? 0)) / 100, 2) }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Orders</div>
                    <div class="text-xl font-semibold">{{ (int) ($totals->orders ?? 0) }}</div>
                </div>
            </div>
            <div class="card p-6 text-gray-700 dark:text-gray-300 space-y-4">
                <h3 class="text-lg font-semibold">Sales</h3>
                <div class="space-y-2">
                    @forelse ($rows as $r)
                        @php($w = (int) round(($r->revenue_cents / ($maxRevenue ?? 1)) * 100))
                        <div class="flex items-center gap-3">
                            <div class="w-24 text-sm text-gray-600 dark:text-gray-400">{{ $r->d }}</div>
                            <div class="flex-1 bg-gray-100 dark:bg-gray-800 rounded h-6 overflow-hidden">
                                <div class="bg-primary text-white text-xs h-6 flex items-center px-2" style="width: {{ $w }}%">
                                    {{ number_format($r->revenue_cents / 100, 2) }} ({{ $r->orders }})
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-gray-500">No sales yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (function() {
            const labels = @json($chart['labels'] ?? []);
            const revenue = @json($chart['revenue'] ?? []);
            const byStatus = @json($chart['byStatus'] ?? null);
            const ctx = document.getElementById('salesChart');
            if (!ctx) return;
            const palette = [
                '#4f46e5','#16a34a','#ef4444','#f59e0b','#06b6d4','#a855f7','#10b981','#f97316','#3b82f6'
            ];
            let datasets = [];
            let type = 'line';
            const options = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: {}
            };
            if (byStatus && Object.keys(byStatus).length) {
                type = 'bar';
                options.scales = { x: { stacked: true }, y: { stacked: true } };
                const statuses = Object.keys(byStatus);
                statuses.forEach((st, idx) => {
                    datasets.push({
                        type: 'bar',
                        label: st,
                        data: byStatus[st],
                        backgroundColor: palette[idx % palette.length],
                        borderWidth: 0,
                    });
                });
            } else {
                datasets.push({
                    type: 'line',
                    label: 'Revenue',
                    data: revenue,
                    tension: 0.3,
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79,70,229,0.15)',
                    fill: true,
                });
            }
            new Chart(ctx, {
                type,
                data: { labels, datasets },
                options,
            });
        })();
    </script>
</x-admin-layout>

