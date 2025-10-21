<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import AiSupportChat from '@/Components/AiSupportChat.vue';

const props = defineProps({
  product: { type: Object, required: true },
  variants: { type: Array, default: () => [] },
});

const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

import { reactive, computed, onMounted, watch, ref } from 'vue';

// Build attribute keys and values from variants
const attributeKeys = computed(() => {
  const keys = new Set();
  (props.variants || []).forEach(v => {
    const attrs = v.attributes || {};
    Object.keys(attrs).forEach(k => keys.add(k));
  });
  return Array.from(keys);
});

const optionsByKey = computed(() => {
  const map = {};
  attributeKeys.value.forEach(k => { map[k] = new Set(); });
  (props.variants || []).forEach(v => {
    const attrs = v.attributes || {};
    attributeKeys.value.forEach(k => {
      if (attrs[k] !== undefined && attrs[k] !== null) {
        map[k].add(String(attrs[k]));
      }
    });
  });
  const out = {};
  Object.keys(map).forEach(k => { out[k] = Array.from(map[k]); });
  return out;
});

const selection = reactive({});
const qty = ref(1);

const selectedVariant = computed(() => {
  if (!props.variants?.length) return null;
  return props.variants.find(v => {
    const attrs = v.attributes || {};
    return attributeKeys.value.every(k => String(attrs[k] ?? '') === String(selection[k] ?? ''));
  }) || null;
});

// Ensure placeholder option shows by initializing empty selections
function initSelections() {
  (attributeKeys.value || []).forEach(k => {
    if (!(k in selection)) selection[k] = '';
  });
}

onMounted(() => {
  initSelections();
});

watch(attributeKeys, () => initSelections());

// Display price: use variant price if selected, otherwise product price
const displayPrice = computed(() => {
  const symbol = String(props.product.price_formatted || '').split(' ')[0] || '$';
  if (selectedVariant.value) {
    const v = selectedVariant.value;
    // Prefer decimal fields if present
    const price = v.price ?? ((v.net_price ?? 0) + (v.tax ?? 0));
    if (price && !Number.isNaN(price)) return `${symbol} ${Number(price).toFixed(2)}`;
    // fallback to legacy cents
    const cents = (v.selling_price_cents ?? v.price_cents);
    if (cents != null) return `${symbol} ${(Number(cents) / 100).toFixed(2)}`;
  }
  return props.product.price_formatted;
});
</script>

<template>
  <Head :title="product.name" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ product.name }}
      </h2>
    </template>

    <div class="py-6">
      <div class="container-narrow">
        <div class="card p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <img v-if="product.image_path" :src="product.image_path.startsWith('/') ? product.image_path : ('/' + product.image_path)" :alt="product.name" class="w-full h-80 object-cover rounded" />
            <div v-else class="w-full h-80 bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400 rounded">No Image</div>
          </div>
          <div class="space-y-4">
            <h1 class="text-2xl font-semibold">{{ product.name }}</h1>
            <p class="text-lg">{{ displayPrice }}</p>
            <p class="text-gray-700 dark:text-gray-300">{{ product.description }}</p>
            <div v-if="variants?.length" class="space-y-4">
              <div v-for="k in attributeKeys" :key="k">
                <label class="input-label">{{ k }}</label>
                <select v-model="selection[k]" class="input-field text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900">
                  <option disabled value="">Select {{ k }}</option>
                  <option v-for="opt in (optionsByKey[k] || [])" :key="opt" :value="opt">{{ opt }}</option>
                </select>
              </div>
              <div v-if="selectedVariant" class="text-sm text-gray-600 dark:text-gray-300">Selected variant: #{{ selectedVariant.id }}</div>
              <div v-else class="text-sm text-red-600">Please select all options.</div>
            </div>

            <form :action="`/cart/add/${product.id}`" method="POST" class="space-y-3">
              <input type="hidden" name="_token" :value="csrf" />
              
              <input v-if="selectedVariant" type="hidden" name="product_variant_id" :value="selectedVariant.id" />
              <div>
                <label class="input-label">Quantity</label>
                <div class="flex items-center gap-2">
                  <button type="button" class="w-10 h-10 flex items-center justify-center border rounded-full font-bold text-xl bg-white hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800" @click="qty = Math.max(1, Number(qty) - 1)">−</button>
                  <input type="number" name="quantity" v-model.number="qty" min="1" class="w-20 input-field text-center" />
                  <button type="button" class="w-10 h-10 flex items-center justify-center border rounded-full font-bold text-xl bg-white hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800" @click="qty = Math.max(1, Number(qty) + 1)">+</button>
                </div>
              </div>
              <button type="submit" class="btn-primary" :disabled="variants?.length && !selectedVariant">Add to Cart</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
  <AiSupportChat context="product" :id="product.id" />
</template>
