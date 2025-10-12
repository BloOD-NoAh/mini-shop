<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Admin Dashboard
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="container-wide space-y-4">
            <div class="card p-6">
                <h3 class="text-lg font-semibold mb-2">Management</h3>
                <div class="flex items-center gap-3">
                    <a href="{{ url('/admin/products') }}" class="btn-primary">Products</a>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

