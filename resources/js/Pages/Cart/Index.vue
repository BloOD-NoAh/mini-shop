<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
  items: { type: Array, default: () => [] },
  subtotalCents: { type: Number, default: 0 },
  currency: { type: String, default: 'usd' },
});

const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

function money(cents, currency) {
  const c = String(currency || 'usd').toLowerCase();
  const symbol = c === 'usd' ? '$' : c === 'eur' ? '€' : c === 'gbp' ? '£' : c === 'jpy' ? '¥' : c.toUpperCase() + ' ';
  return `${symbol} ${(Number(cents || 0) / 100).toFixed(2)}`;
}
</script>

<template>
  <Head title="Your Cart" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Your Cart</h2>
    </template>

    <div class="py-6">
      <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 shadow rounded p-4">
          <p v-if="!items.length" class="text-gray-600 dark:text-gray-300">Your cart is empty.</p>
          <div v-else class="divide-y divide-gray-200 dark:divide-gray-700">
            <div v-for="i in items" :key="i.product.id" class="py-4 flex items-center justify-between gap-4">
              <div class="flex items-center gap-4">
                <img v-if="i.product.image_path" :src="i.product.image_path.startsWith('/') ? i.product.image_path : ('/' + i.product.image_path)" :alt="i.product.name" class="w-16 h-16 object-cover rounded" />
                <div v-else class="w-16 h-16 bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-400 rounded">—</div>
                <div>
                  <a :href="`/products/${i.product.slug}`" class="font-medium text-gray-900 dark:text-gray-100 hover:text-indigo-600">{{ i.product.name }}</a>
                  <div class="text-gray-700 dark:text-gray-300">{{ i.product.price_formatted || money(i.product.price_cents, currency) }}</div>
                </div>
              </div>
              <div class="flex items-center gap-3">
                <form :action="`/cart/update/${i.product.id}`" method="POST" class="flex items-center gap-2">
                  <input type="hidden" name="_token" :value="csrf" />
                  <input type="number" name="quantity" :value="i.quantity" min="1" class="w-20 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                  <button class="px-3 py-2 bg-gray-800 text-white text-sm rounded hover:bg-gray-900">Update</button>
                </form>
                <form :action="`/cart/remove/${i.product.id}`" method="POST">
                  <input type="hidden" name="_token" :value="csrf" />
                  <input type="hidden" name="_method" value="DELETE" />
                  <button class="px-3 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700">Remove</button>
                </form>
              </div>
            </div>

            <div class="mt-6 flex items-center justify-between">
              <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">Subtotal: {{ money(subtotalCents, currency) }}</div>
              <a href="/checkout" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Checkout</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
  
</template>

