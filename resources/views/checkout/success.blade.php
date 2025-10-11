<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Payment Successful</h2>
    </x-slot>
    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded p-6 space-y-4">
                <p class="text-gray-800">Thank you! Your order has been placed.</p>
                <a href="{{ url('/orders') }}" class="text-indigo-600 hover:underline">View orders</a>
                <a href="{{ url('/') }}" class="text-indigo-600 hover:underline">Continue shopping</a>
            </div>
        </div>
    </div>
</x-app-layout>

