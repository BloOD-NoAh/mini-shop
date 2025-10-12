<template>
  <div>
    <h1 class="text-xl font-semibold mb-4">Products</h1>
    <div v-if="flash" class="mb-4 p-4 rounded bg-green-100 text-green-800">{{ flash }}</div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <div v-for="p in products.data" :key="p.id" class="card overflow-hidden">
        <img v-if="p.image_path" :src="asset(p.image_path)" :alt="p.name" class="w-full h-40 object-cover" />
        <div v-else class="w-full h-40 bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400">No Image</div>
        <div class="p-4 space-y-2">
          <RouterLink :to="`/app/products/${p.slug}`" class="block font-semibold hover:text-primary">{{ p.name }}</RouterLink>
          <p class="text-gray-700 dark:text-gray-300">$ {{ formatPrice(p.price_cents) }}</p>
          <form @submit.prevent="addToCart(p.id)">
            <div class="flex items-center gap-2">
              <input v-model.number="qty[p.id]" type="number" min="1" class="w-20 input-field" />
              <button class="btn-primary">Add to Cart</button>
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
</template>

<script setup>
import axios from 'axios';
import { onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

const products = reactive({ data: [], current_page: 1, last_page: 1, links: {} });
const qty = reactive({});
const flash = ref('');

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

onMounted(() => fetchProducts());
</script>


