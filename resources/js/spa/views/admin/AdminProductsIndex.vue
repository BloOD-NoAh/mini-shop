<template>
  <div class=\"md:flex\">
    <AdminSidebar />
    <div class=\"flex-1\">
      <AdminTopbar @toggle-theme=\"toggleTheme\" />
      
  <div>
    <div class="flex items-center justify-between">
      <h1 class="text-xl font-semibold mb-4">Admin • Products</h1>
      <RouterLink to="/app/admin/products/create" class="btn-primary">New Product</RouterLink>
    </div>
    <div v-if="flash" class="mb-4 p-4 rounded bg-green-100 text-green-800">{{ flash }}</div>
    <div class="bg-white shadow rounded overflow-x-auto">
      <table class="table">
        <thead class="thead">
          <tr>
            <th class="th">ID</th>
            <th class="th">Name</th>
            <th class="th">Price</th>
            <th class="th">Net</th>
            <th class="th">Tax</th>
            <th class="th">Selling</th>
            <th class="th">Stock</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody class="tbody">
          <tr v-for="p in products.data" :key="p.id">
            <td class="td">{{ p.id }}</td>
            <td class="td">{{ p.name }}</td>
            <td class="td">$ {{ formatPrice(p.price_cents) }}</td>
            <td class="td">$ {{ formatDec(p.net_price_cents) }}</td>
            <td class="td">$ {{ formatDec(p.tax_cents) }}</td>
            <td class="td">$ {{ formatDec(p.selling_price_cents) }}</td>
            <td class="td">{{ p.stock }}</td>
            <td class="px-4 py-2 text-right space-x-2">
              <RouterLink :to="`/app/admin/products/${p.id}/edit`" class="btn-muted">Edit</RouterLink>
              <button @click="destroy(p.id)" class="btn-danger">Delete</button>
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

    </div>
  </div>
</template><script setup>
import AdminTopbar from '../../components/AdminTopbar.vue';
import AdminSidebar from '../../components/AdminSidebar.vue';



import axios from 'axios';
import { onMounted, reactive, ref } from 'vue';

const products = reactive({ data: [], current_page: 1, last_page: 1, links: {} });
const flash = ref('');

function formatPrice(c) { return (c / 100).toFixed(2); }
function formatDec(x) { return Number(x ?? 0).toFixed(2); }

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







function toggleTheme(){
  const root = document.documentElement;
  const isDark = root.classList.toggle('dark');
  try { localStorage.setItem('theme', isDark ? 'dark' : 'light'); } catch(e) {}
}
</script>

