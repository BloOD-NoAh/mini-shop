<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Payment Settings
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="container-wide space-y-4">
            @if (session('status'))
                <div class="p-3 rounded bg-green-50 text-green-800 dark:bg-green-900/30 dark:text-green-200">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="p-3 rounded bg-red-50 text-red-800 dark:bg-red-900/30 dark:text-red-200">
                    {{ implode(', ', $errors->all()) }}
                </div>
            @endif

            <div class="card p-6 space-y-4">
                <form method="POST" action="{{ route('admin.payments.update') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 rounded border dark:border-gray-700">
                            <div class="flex items-center justify-between mb-2">
                                <div class="font-semibold">Stripe</div>
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" name="methods[stripe]" {{ ($enabled['stripe'] ?? false) ? 'checked' : '' }}>
                                    <span>Enabled</span>
                                </label>
                            </div>
                            <div class="text-sm">API keys: {!! $stripeConfigured ? '<span class="text-green-600">Configured</span>' : '<span class="text-red-600">Not configured</span>' !!}</div>
                        </div>

                        <div class="p-4 rounded border dark:border-gray-700">
                            <div class="flex items-center justify-between mb-2">
                                <div class="font-semibold">PayPal</div>
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" name="methods[paypal]" {{ ($enabled['paypal'] ?? false) ? 'checked' : '' }}>
                                    <span>Enabled</span>
                                </label>
                            </div>
                            <div class="text-sm">Client ID/Secret: {!! $paypalConfigured ? '<span class="text-green-600">Configured</span>' : '<span class="text-red-600">Not configured</span>' !!}</div>
                            <div class="text-sm mt-1">Mode: <span class="font-mono">{{ ucfirst($paypalMode) }}</span></div>
                        </div>
                    </div>

                    <div class="pt-2">
                        <div class="text-sm text-gray-600 dark:text-gray-300 mb-2">Default currency: <span class="font-mono uppercase">{{ $currency }}</span></div>
                        <button class="btn-primary">Save</button>
                    </div>
                </form>

                <div class="text-sm text-gray-600 dark:text-gray-300">
                    Note: Configure Stripe keys in <span class="font-mono">.env</span> as <span class="font-mono">STRIPE_KEY</span>/<span class="font-mono">STRIPE_SECRET</span> and PayPal in <span class="font-mono">PAYPAL_CLIENT_ID</span>/<span class="font-mono">PAYPAL_SECRET</span>.
                    Expose PayPal client id to the frontend via <span class="font-mono">VITE_PAYPAL_CLIENT_ID</span>.
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

