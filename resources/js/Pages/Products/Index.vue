<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, reactive, computed, onMounted, watch } from 'vue';

const props = defineProps({
  products: { type: Object, required: true },
  categories: { type: Array, default: () => [] },
  activeCategory: { type: String, default: '' },
  q: { type: String, default: '' },
});

const query = ref(props.q || '');
const localFlash = ref('');
const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

function submitSearch() {
  router.get(route('home'), { q: query.value }, { preserveState: true, replace: true });
}

function goCategory(cat) {
  const params = { ...(query.value ? { q: query.value } : {}), ...(cat ? { category: cat } : {}) };
  router.get(route('home'), params, { preserveState: true, replace: true });
}

function goPage(url) {
  if (!url) return;
  router.get(url, {}, { preserveState: true, replace: true });
}

// quantity is fixed at 1; input is disabled and a hidden field is submitted

// Variant selection modal state
const showVariantModal = ref(false);
const modalProduct = ref(null);
const modalVariants = ref([]);
const modalQty = ref(1);
const selection = reactive({});

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

watch(attributeKeys, () => initSelections());

async function openVariant(product) {
  try {
    const res = await fetch(`/products/${product.slug}`, { headers: { Accept: 'application/json' } });
    const data = await res.json();
    modalProduct.value = data;
    modalVariants.value = data.variants || [];
    modalQty.value = 1;
    // reset selections
    Object.keys(selection).forEach(k => delete selection[k]);
    initSelections();
    showVariantModal.value = true;
  } catch (e) {
    // fallback to page if fetch fails
    window.location.href = `/products/${product.slug}`;
  }
}

function closeVariantModal() {
  showVariantModal.value = false;
}

async function submitVariantAdd() {
  if (!modalProduct.value) return;
  try {
    const payload = { quantity: modalQty.value };
    if (selectedVariant.value) payload.product_variant_id = selectedVariant.value.id;
    const res = await fetch(`/cart/add/${modalProduct.value.id}`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      body: JSON.stringify(payload),
    });
    if (!res.ok) {
      const err = await res.json().catch(() => ({}));
      throw new Error(err?.message || 'Failed to add to cart');
    }
    const data = await res.json();
    localFlash.value = data?.message || 'Added to cart';
    showVariantModal.value = false;
    setTimeout(() => (localFlash.value = ''), 2000);
  } catch (e) {
    localFlash.value = e?.message || 'Failed to add to cart';
    setTimeout(() => (localFlash.value = ''), 2500);
  }
}
</script>

<template>
  <Head title="Mini Shop" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Mini Shop</h2>
    </template>

    <div class="py-6">
      <div class="container-wide">
        <div v-if="$page.props.flash?.success" class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ $page.props.flash.success }}</div>
        <div v-if="localFlash" class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ localFlash }}</div>
        <div class="mb-6">
          <form @submit.prevent="submitSearch" class="flex items-center gap-2">
            <input v-model="query" type="text" placeholder="Search products..." class="input-field" />
            <button type="submit" class="btn-muted">Search</button>
          </form>

          <div v-if="categories?.length" class="mt-3 flex flex-wrap gap-2">
            <button @click="goCategory('')"
                    class="px-3 py-1 rounded-full text-sm border"
                    :class="!activeCategory ? 'bg-primary text-white border-primary-600 dark:bg-primary dark:border-primary-600 hover:bg-primary-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 dark:bg-gray-900 dark:text-gray-200 dark:border-gray-700 dark:hover:bg-gray-800'">
              All
            </button>
            <button v-for="cat in categories" :key="cat" @click="goCategory(cat)"
                    class="px-3 py-1 rounded-full text-sm border"
                    :class="activeCategory === cat ? 'bg-primary text-white border-primary-600 dark:bg-primary dark:border-primary-600 hover:bg-primary-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50 dark:bg-gray-900 dark:text-gray-200 dark:border-gray-700 dark:hover:bg-gray-800'">
              {{ cat }}
            </button>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
          <div v-for="product in products.data" :key="product.id" class="card overflow-hidden">
            <div>
              <img v-if="product.image_path" :src="product.image_path.startsWith('/') ? product.image_path : ('/' + product.image_path)" :alt="product.name" class="w-full h-40 object-cover" />
              <div v-else class="w-full h-40 bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">No Image</div>
            </div>
            <div class="p-4 space-y-2">
              <a :href="`/products/${product.slug}`" class="block font-semibold hover:text-primary">{{ product.name }}</a>
              <div v-if="product.category" class="badge-muted inline-block">{{ product.category }}</div>
              <p class="text-gray-700 dark:text-gray-300">{{ product.price_formatted }}</p>
              <template v-if="(product.variants_count || 0) > 0">
                <button type="button" class="btn-muted" @click="openVariant(product)">Choose Options</button>
              </template>
              <form v-else :action="`/cart/add/${product.id}`" method="POST" class="flex items-center gap-2">
                <input type="hidden" name="_token" :value="csrf" />
                <div class="flex items-center gap-2">
                  <button type="button" class="w-8 h-8 flex items-center justify-center border rounded-full font-bold text-lg bg-white hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800" @click="
                    const input = $event.currentTarget.closest('form').querySelector('input[name=quantity]');
                    input.value = String(Math.max(1, (parseInt(input.value||'1',10)-1)));
                  ">−</button>
                  <input type="number" name="quantity" value="1" min="1" class="w-14 input-field text-center">
                  <button type="button" class="w-8 h-8 flex items-center justify-center border rounded-full font-bold text-lg bg-white hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-gray-800" @click="
                    const input = $event.currentTarget.closest('form').querySelector('input[name=quantity]');
                    input.value = String(Math.max(1, (parseInt(input.value||'1',10)+1)));
                  ">+</button>
                </div>
                <button type="submit" class="btn-primary whitespace-nowrap">Add</button>
              </form>
            </div>
          </div>
        </div>

        <div v-if="products.links?.length" class="mt-6 flex flex-wrap gap-2">
          <button v-for="l in products.links" :key="l.label" @click="goPage(l.url)" :disabled="!l.url"
                  class="px-3 py-1 rounded border text-sm"
                  :class="[l.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 dark:bg-gray-900 dark:text-gray-200 dark:border-gray-700', !l.url ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-50 dark:hover:bg-gray-800']"
                  v-html="l.label" />
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
  
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
                <input type="number" name="quantity" v-model.number="modalQty" min="1" class="w-20 input-field text-center" />
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
