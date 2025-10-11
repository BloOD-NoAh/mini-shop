<template>
  <div>
    <div class="flex items-center justify-between">
      <h1 class="text-xl font-semibold mb-4">Admin • Products</h1>
      <RouterLink to="/app/admin/products/create" class="px-3 py-2 bg-indigo-600 text-white rounded">New Product</RouterLink>
    </div>
    <div v-if="flash" class="mb-4 p-4 rounded bg-green-100 text-green-800">{{ flash }}</div>
    <div class="bg-white shadow rounded overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="p in products.data" :key="p.id">
            <td class="px-4 py-2 text-sm text-gray-700">{{ p.id }}</td>
            <td class="px-4 py-2 text-sm text-gray-900">{{ p.name }}</td>
            <td class="px-4 py-2 text-sm text-gray-900">$ {{ formatPrice(p.price_cents) }}</td>
            <td class="px-4 py-2 text-sm text-gray-900">{{ p.stock }}</td>
            <td class="px-4 py-2 text-right space-x-2">
              <RouterLink :to="`/app/admin/products/${p.id}/edit`" class="px-3 py-1 bg-gray-800 text-white text-sm rounded">Edit</RouterLink>
              <button @click="destroy(p.id)" class="px-3 py-1 bg-red-600 text-white text-sm rounded">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="mt-4 flex items-center gap-2">
      <button class="px-3 py-1 rounded bg-gray-200" :disabled="!products.links.prev" @click="goPage(products.current_page - 1)">Prev</button>
      <div>Page {{ products.current_page }} of {{ products.last_page }}</div>
      <button class="px-3 py-1 rounded bg-gray-200" :disabled="!products.links.next" @click="goPage(products.current_page + 1)">Next</button>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios';
import { onMounted, reactive, ref } from 'vue';

const products = reactive({ data: [], current_page: 1, last_page: 1, links: {} });
const flash = ref('');

function formatPrice(c) { return (c / 100).toFixed(2); }

async function fetchProducts(page = 1) {
  const res = await axios.get('/admin/products', { headers: { Accept: 'application/json' }, params: { page } });
  const d = res.data;
  products.data = d.data;
  products.current_page = d.current_page;
  products.last_page = d.last_page;
  products.links = { prev: d.prev_page_url, next: d.next_page_url };
}

function goPage(page) { if (page >= 1 && page <= products.last_page) fetchProducts(page); }

async function destroy(id) {
  await axios.delete(`/admin/products/${id}`);
  await fetchProducts(products.current_page);
  flash.value = 'Product deleted';
  setTimeout(() => (flash.value = ''), 1500);
}

onMounted(() => fetchProducts());
</script>

