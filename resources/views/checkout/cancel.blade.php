<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Payment Canceled</h2>
    </x-slot>
    <div class="py-6">
        <div class="container-narrow">
            <div class="card space-y-4">
                <p class="text-gray-800">Your payment was canceled. You can try again or return to your cart.</p>
                <a href="{{ url('/cart') }}" class="text-primary hover:underline">Back to cart</a>
            </div>
        </div>
    </div>
</x-app-layout>


