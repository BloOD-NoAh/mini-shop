<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
  products: { type: Object, required: true },
  categories: { type: Array, default: () => [] },
  activeCategory: { type: String, default: '' },
  q: { type: String, default: '' },
});

const query = ref(props.q || '');
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
</script>

<template>
  <Head title="Products" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Products</h2>
    </template>

    <div class="py-6">
      <div class="container-wide">
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
              <form :action="`/cart/add/${product.id}`" method="POST" class="flex items-center gap-2">
                <input type="hidden" name="_token" :value="csrf" />
                <input type="number" name="quantity" value="1" min="1" class="w-20 input-field">
                <button type="submit" class="btn-primary">Add to Cart</button>
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
  
</template>
