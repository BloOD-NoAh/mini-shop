<template>
  <div>
    <h1 class="text-xl font-semibold mb-4">Mini Shop</h1>
    <div v-if="flash" class="mb-4 p-4 rounded bg-green-100 text-green-800">{{ flash }}</div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <div v-for="p in products.data" :key="p.id" class="card overflow-hidden">
        <img v-if="p.image_path" :src="asset(p.image_path)" :alt="p.name" class="w-full h-40 object-cover" />
        <div v-else class="w-full h-40 bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">No Image</div>
        <div class="p-4 space-y-2">
          <RouterLink :to="`/app/products/${p.slug}`" class="block font-semibold hover:text-primary">{{ p.name }}</RouterLink>
          <p class="text-gray-700 dark:text-gray-300">$ {{ formatPrice(p.price_cents) }}</p>
          <template v-if="(p.variants_count || 0) > 0">
            <button type="button" class="btn-muted" @click="openVariant(p)">Choose Options</button>
          </template>
          <form v-else @submit.prevent="addToCart(p.id)">
            <div class="flex items-center gap-2">
              <button type="button" class="w-8 h-8 flex items-center justify-center border rounded-full font-bold text-lg bg-white hover:bg-gray-50" @click="qty[p.id] = Math.max(1, Number(qty[p.id]||1) - 1)">−</button>
              <input v-model.number="qty[p.id]" type="number" min="1" class="w-16 input-field text-center" />
              <button type="button" class="w-8 h-8 flex items-center justify-center border rounded-full font-bold text-lg bg-white hover:bg-gray-50" @click="qty[p.id] = Math.max(1, Number(qty[p.id]||1) + 1)">+</button>
              <button class="btn-primary whitespace-nowrap">Add</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="mt-6 flex items-center gap-2" v-if="products.links">
      <button class="px-3 py-1 rounded bg-gray-200" :disabled="!products.links.prev" @click="goPage(products.current_page - 1)">Prev</button>
      <div>Page {{ products.current_page }} of {{ products.last_page }}</div>
      <button class="px-3 py-1 rounded bg-gray-200" :disabled="!products.links.next" @click="goPage(products.current_page + 1)">Next</button>
    </div>
  </div>
  
  <!-- Variant selection modal -->
  <div v-if="showVariantModal" class="fixed inset-0 z-40 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/40" @click="closeVariantModal"></div>
    <div class="relative z-50 w-full max-w-lg mx-4">
      <div class="card p-5 bg-white dark:bg-gray-900">
        <div class="flex items-start justify-between mb-3">
          <h3 class="text-lg font-semibold">{{ modalProduct?.name || 'Select Options' }}</h3>
          <button type="button" class="btn-muted" @click="closeVariantModal">Close</button>
        </div>
        <div class="space-y-4">
          <div v-if="attributeKeys.length">
            <div v-for="k in attributeKeys" :key="k" class="mb-3">
              <label class="input-label">{{ k }}</label>
              <select v-model="selection[k]" class="input-field text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-900">
                <option disabled value="">Select {{ k }}</option>
                <option v-for="opt in (optionsByKey[k] || [])" :key="opt" :value="opt">{{ opt }}</option>
              </select>
            </div>
            <div v-if="!selectedVariant" class="text-sm text-red-600">Please select all options.</div>
          </div>

          <div v-if="modalProduct" class="space-y-3">
            <div>
              <label class="input-label">Quantity</label>
              <div class="flex items-center gap-2">
                <button type="button" class="w-10 h-10 flex items-center justify-center border rounded-full font-bold text-xl bg-white hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800" @click="modalQty = Math.max(1, Number(modalQty) - 1)">−</button>
                <input type="number" v-model.number="modalQty" min="1" class="w-20 input-field text-center" />
                <button type="button" class="w-10 h-10 flex items-center justify-center border rounded-full font-bold text-xl bg-white hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800" @click="modalQty = Math.max(1, Number(modalQty) + 1)">+</button>
              </div>
            </div>
            <div class="flex items-center justify-end gap-2">
              <button type="button" class="btn-muted" @click="closeVariantModal">Cancel</button>
              <button type="button" class="btn-primary" :disabled="attributeKeys.length && !selectedVariant" @click="submitVariantAdd">Add to Cart</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios';
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

const products = reactive({ data: [], current_page: 1, last_page: 1, links: {} });
const qty = reactive({});
const flash = ref('');
const showVariantModal = ref(false);
const modalProduct = ref(null);
const modalVariants = ref([]);
const modalQty = ref(1);
const selection = reactive({});

function asset(path) {
  return `/${path}`;
}

function formatPrice(cents) {
  return (cents / 100).toFixed(2);
}

async function fetchProducts(page = 1) {
  const res = await axios.get('/', { params: { page } });
  const d = res.data;
  products.data = d.data;
  products.current_page = d.current_page;
  products.last_page = d.last_page;
  products.links = { prev: d.prev_page_url, next: d.next_page_url };
  for (const p of d.data) if (!qty[p.id]) qty[p.id] = 1;
}

function goPage(page) {
  if (page < 1 || page > products.last_page) return;
  fetchProducts(page);
}

async function addToCart(productId) {
  try {
    await axios.post(`/cart/add/${productId}`, { quantity: qty[productId] || 1 });
    flash.value = 'Added to cart';
    setTimeout(() => (flash.value = ''), 2000);
  } catch (e) {
    flash.value = e.response?.data?.message || 'Failed to add to cart';
  }
}

const attributeKeys = computed(() => {
  const keys = new Set();
  (modalVariants.value || []).forEach(v => {
    const attrs = v.attributes || {};
    Object.keys(attrs).forEach(k => keys.add(k));
  });
  return Array.from(keys);
});

const optionsByKey = computed(() => {
  const map = {};
  attributeKeys.value.forEach(k => { map[k] = new Set(); });
  (modalVariants.value || []).forEach(v => {
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

const selectedVariant = computed(() => {
  if (!modalVariants.value?.length) return null;
  return modalVariants.value.find(v => {
    const attrs = v.attributes || {};
    return attributeKeys.value.every(k => String(attrs[k] ?? '') === String(selection[k] ?? ''));
  }) || null;
});

function initSelections() {
  (attributeKeys.value || []).forEach(k => { if (!(k in selection)) selection[k] = ''; });
}

async function openVariant(product) {
  try {
    const res = await axios.get(`/products/${product.slug}`, { headers: { Accept: 'application/json' } });
    const data = res.data;
    modalProduct.value = data;
    modalVariants.value = data.variants || [];
    modalQty.value = 1;
    Object.keys(selection).forEach(k => delete selection[k]);
    initSelections();
    showVariantModal.value = true;
  } catch (e) {
    router.push(`/app/products/${product.slug}`);
  }
}

function closeVariantModal() { showVariantModal.value = false; }

async function submitVariantAdd() {
  if (!modalProduct.value) return;
  try {
    const payload = { quantity: modalQty.value };
    if (selectedVariant.value) payload.product_variant_id = selectedVariant.value.id;
    await axios.post(`/cart/add/${modalProduct.value.id}`, payload);
    flash.value = 'Added to cart';
    showVariantModal.value = false;
    setTimeout(() => (flash.value = ''), 2000);
  } catch (e) {
    flash.value = e.response?.data?.message || 'Failed to add to cart';
  }
}

onMounted(() => {
  fetchProducts();
  if (route.query?.added === '1') {
    flash.value = 'Added to cart';
    setTimeout(() => (flash.value = ''), 2000);
    // clean query
    const q = new URLSearchParams(route.query);
    q.delete('added');
    router.replace({ path: route.path, query: Object.fromEntries(q.entries()) });
  }
});
</script>


