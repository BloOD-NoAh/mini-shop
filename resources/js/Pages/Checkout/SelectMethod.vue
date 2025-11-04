<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Choose Payment Method</h2>
    </template>

    <div class="py-6">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded p-6 space-y-4">
          <div class="text-lg">Total: $ {{ (amountCents / 100).toFixed(2) }} {{ (currency || '').toUpperCase() }}</div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a v-if="methods.includes('stripe')" :href="route('checkout.create', { method: 'stripe' })" class="p-4 rounded border hover:border-indigo-400 transition">
              <div class="font-medium mb-1">Pay with Card (Stripe)</div>
              <div class="text-sm text-gray-600">Cards, wallets, and more via Stripe.</div>
            </a>
            <a v-if="methods.includes('paypal')" :href="route('checkout.create', { method: 'paypal' })" class="p-4 rounded border hover:border-indigo-400 transition">
              <div class="font-medium mb-1">Pay with PayPal</div>
              <div class="text-sm text-gray-600">Checkout using your PayPal account.</div>
            </a>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({
  methods: { type: Array, required: true },
  amountCents: { type: Number, required: true },
  currency: { type: String, required: true },
});
</script>

