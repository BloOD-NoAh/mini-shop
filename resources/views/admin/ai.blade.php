<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            AI Assistant Settings
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

            <div class="card p-6">
                <form method="POST" action="{{ route('admin.ai.update') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="input-label">Active Provider</label>
                        <select name="provider" class="input-field w-64">
                            @foreach (array_keys($providers) as $name)
                                <option value="{{ $name }}" {{ $currentProvider === $name ? 'selected' : '' }}>{{ ucfirst($name) }}</option>
                            @endforeach
                        </select>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">Default is Gemini. Choose which AI model to use for the support chat.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach ($providers as $name => $cfg)
                            <div class="p-4 rounded border dark:border-gray-700">
                                <div class="font-semibold mb-2">{{ ucfirst($name) }}</div>
                                <div class="text-sm mb-2">API key: {!! $cfg['api_key'] ? '<span class="text-green-600">Configured</span>' : '<span class="text-red-600">Not configured</span>' !!}</div>
                                <label class="input-label">Model</label>
                                <select name="models[{{ $name }}]" class="input-field w-full">
                                    @foreach ($cfg['available_models'] as $model)
                                        <option value="{{ $model }}" {{ ($currentModels[$name] ?? $cfg['default_model']) === $model ? 'selected' : '' }}>{{ $model }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    </div>

                    <div>
                        <button class="btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>

