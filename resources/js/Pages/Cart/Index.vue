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

function decLine(ev) {
  const form = ev.currentTarget?.closest('form');
  if (!form) return;
  const input = form.querySelector('input[name="quantity"]');
  if (!input) return;
  const val = Math.max(1, parseInt(input.value || '1', 10) - 1);
  input.value = String(val);
  try { form.submit(); } catch (_) {}
}

function incLine(ev) {
  const form = ev.currentTarget?.closest('form');
  if (!form) return;
  const input = form.querySelector('input[name="quantity"]');
  if (!input) return;
  const val = Math.max(1, parseInt(input.value || '1', 10) + 1);
  input.value = String(val);
  try { form.submit(); } catch (_) {}
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
            <div v-for="i in items" :key="i.id" class="py-4 flex items-center justify-between gap-4">
              <div class="flex items-center gap-4">
                <img v-if="i.product.image_path" :src="i.product.image_path.startsWith('/') ? i.product.image_path : ('/' + i.product.image_path)" :alt="i.product.name" class="w-16 h-16 object-cover rounded" />
                <div v-else class="w-16 h-16 bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-400 rounded">—</div>
                <div>
                  <a :href="`/products/${i.product.slug}`" class="font-medium text-gray-900 dark:text-gray-100 hover:text-indigo-600">{{ i.product.name }}</a>
                  <div v-if="i.variant && i.variant.attributes" class="text-xs text-gray-600 dark:text-gray-400">
                    <span v-for="(val, key) in i.variant.attributes" :key="key" class="mr-2">{{ key }}: {{ val }}</span>
                  </div>
                  <div class="text-gray-700 dark:text-gray-300">{{ money(i.unit_price_cents, currency) }}</div>
                </div>
              </div>
              <div class="flex items-center gap-3">
                <form :action="`/cart/update-item/${i.id}`" method="POST" class="flex items-center gap-2">
                  <input type="hidden" name="_token" :value="csrf" />
                  <button type="button" class="w-8 h-8 flex items-center justify-center border rounded-full font-bold text-lg bg-white hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800" @click="decLine($event)">−</button>
                  <input type="number" name="quantity" :value="i.quantity" min="1" class="w-16 text-center rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" />
                  <button type="button" class="w-8 h-8 flex items-center justify-center border rounded-full font-bold text-lg bg-white hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800" @click="incLine($event)">+</button>
                  <button class="px-3 py-2 bg-gray-800 text-white text-sm rounded hover:bg-gray-900">Update</button>
                </form>
                <form :action="`/cart/remove-item/${i.id}`" method="POST">
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
