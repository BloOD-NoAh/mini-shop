<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Payment Canceled</h2>
    </x-slot>
    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded p-6 space-y-4">
                <p class="text-gray-800">Your payment was canceled. You can try again or return to your cart.</p>
                <a href="{{ url('/cart') }}" class="text-indigo-600 hover:underline">Back to cart</a>
            </div>
        </div>
    </div>
</x-app-layout>

