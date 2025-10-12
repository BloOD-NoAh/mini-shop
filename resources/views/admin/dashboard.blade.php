<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Admin Dashboard
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white overflow-hidden shadow rounded p-6">
                <h3 class="text-lg font-semibold mb-2">Management</h3>
                <div class="flex items-center gap-3">
                    <a href="{{ url('/admin/products') }}" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Products</a>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
