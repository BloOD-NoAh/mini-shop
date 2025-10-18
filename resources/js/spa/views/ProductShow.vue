<template>
  <div>
    <div v-if="product" class="card p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <img v-if="product.image_path" :src="asset(product.image_path)" :alt="product.name" class="w-full h-80 object-cover rounded" />
        <div v-else class="w-full h-80 bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400 rounded">No Image</div>
      </div>
      <div class="space-y-4">
        <h1 class="text-2xl font-semibold">{{ product.name }}</h1>
        <p class="text-lg">$ {{ displayPrice }}</p>
        <p class="text-gray-700 dark:text-gray-300">{{ product.description }}</p>
        <div v-if="variants.length" class="space-y-3">
          <div v-for="k in attributeKeys" :key="k">
            <label class="input-label">{{ k }}</label>
            <select v-model="selection[k]" class="input-field text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900">
              <option disabled value="">Select {{ k }}</option>
              <option v-for="opt in (optionsByKey[k] || [])" :key="opt" :value="opt">{{ opt }}</option>
            </select>
          </div>
          <div v-if="selectedVariant" class="text-sm text-gray-600">Selected variant: #{{ selectedVariant.id }}</div>
          <div v-else class="text-sm text-red-600">Please select all options.</div>
        </div>
        <form @submit.prevent="addToCart">
          <label class="input-label">Quantity</label>
          <div class="flex items-center gap-2">
            <button type="button" class="w-10 h-10 flex items-center justify-center border rounded-full font-bold text-xl bg-white hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800" @click="quantity = Math.max(1, Number(quantity) - 1)">−</button>
            <input v-model.number="quantity" type="number" min="1" class="w-20 input-field text-center" />
            <button type="button" class="w-10 h-10 flex items-center justify-center border rounded-full font-bold text-xl bg-white hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800" @click="quantity = Math.max(1, Number(quantity) + 1)">+</button>
            <button class="btn-primary" :disabled="variants.length && !selectedVariant">Add to Cart</button>
          </div>
        </form>
        <div v-if="flash" class="badge-success">{{ flash }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios';
import { ref, onMounted, computed, reactive, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
const product = ref(null);
const quantity = ref(1);
const flash = ref('');
const variants = ref([]);
const selection = reactive({});

function asset(path) { return `/${path}`; }
function centsToDollars(c) { return (c / 100).toFixed(2); }

async function fetchProduct() {
  const res = await axios.get(`/products/${route.params.slug}`);
  product.value = res.data;
  variants.value = res.data.variants || [];
}

const attributeKeys = computed(() => {
  const keys = new Set();
  (variants.value || []).forEach(v => {
    const attrs = v.attributes || {};
    Object.keys(attrs).forEach(k => keys.add(k));
  });
  return Array.from(keys);
});

const optionsByKey = computed(() => {
  const map = {};
  attributeKeys.value.forEach(k => { map[k] = new Set(); });
  (variants.value || []).forEach(v => {
    const attrs = v.attributes || {};
    attributeKeys.value.forEach(k => {
      if (attrs[k] !== undefined && attrs[k] !== null) map[k].add(String(attrs[k]));
    });
  });
  const out = {};
  Object.keys(map).forEach(k => { out[k] = Array.from(map[k]); });
  return out;
});

const selectedVariant = computed(() => {
  if (!variants.value?.length) return null;
  return variants.value.find(v => {
    const attrs = v.attributes || {};
    return attributeKeys.value.every(k => String(attrs[k] ?? '') === String(selection[k] ?? ''));
  }) || null;
});

const displayPrice = computed(() => {
  if (selectedVariant.value) {
    const v = selectedVariant.value;
    const price = v.price ?? ((v.net_price ?? 0) + (v.tax ?? 0));
    if (price && !Number.isNaN(price)) return Number(price).toFixed(2);
    const cents = (v.selling_price_cents ?? v.price_cents);
    if (cents != null) return centsToDollars(cents);
  }
  // Fallback to product pricing
  if (product.value?.selling_price_cents != null) return Number(product.value.selling_price_cents).toFixed(2);
  if (product.value?.price_cents != null) return centsToDollars(product.value.price_cents);
  return '0.00';
});

function initSelections() {
  (attributeKeys.value || []).forEach(k => {
    if (!(k in selection)) selection[k] = '';
  });
}

watch(attributeKeys, () => initSelections());

async function addToCart() {
  try {
    const payload = { quantity: quantity.value };
    if (selectedVariant.value) payload.product_variant_id = selectedVariant.value.id;
    await axios.post(`/cart/add/${product.value.id}`, payload);
    // Redirect to home; let home show a flash via query
    router.push({ path: '/', query: { added: '1' } });
  } catch (e) {
    flash.value = e.response?.data?.message || 'Failed to add to cart';
  }
}

onMounted(fetchProduct);
onMounted(() => initSelections());
</script>


