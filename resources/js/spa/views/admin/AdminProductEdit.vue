<template>
  <div class=\"md:flex\">
    <AdminSidebar />
    <div class=\"flex-1\">
      <AdminTopbar @toggle-theme=\"toggleTheme\" />
      
  <div>
    <h1 class="text-xl font-semibold mb-4">Edit Product #{{ id }}</h1>
    <div v-if="errors.length" class="mb-4 p-4 rounded bg-red-100 text-red-800">
      <ul class="list-disc ml-5"><li v-for="(e,i) in errors" :key="i">{{ e }}</li></ul>
    </div>
    <form @submit.prevent="submit" class="card">
      <div>
        <label class="input-label">Name</label>
        <input v-model="form.name" class="input-field" required />
      </div>
      <div>
        <label class="input-label">Slug</label>
        <input v-model="form.slug" class="input-field" required />
      </div>
      <div>
        <label class="input-label">Description</label>
        <textarea v-model="form.description" class="input-field" rows="4" />
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="input-label">Price</label>
          <input type="number" v-model.number="form.price_cents" min="0" class="input-field"   step="0.01" />
        </div>
        <div>
          <label class="input-label">Stock</label>
          <input type="number" v-model.number="form.stock" min="0" class="input-field" required />
        </div>
        <div>
          <label class="input-label">Image Path</label>
          <input v-model="form.image_path" class="input-field" />
        </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="input-label">Net Price</label>
          <input type="number" v-model.number="form.net_price_cents" min="0" class="input-field"  step="0.01" />
        </div>
        <div>
          <label class="input-label">Tax</label>
          <input type="number" v-model.number="form.tax_cents" min="0" class="input-field"  step="0.01" />
        </div>
        <div>
          <label class="input-label">Selling Price</label>
          <input type="number" v-model.number="form.selling_price_cents" min="0" class="input-field"  step="0.01" />
        </div>
      </div>
      </div>
      <div class="flex items-center justify-end gap-2">
        <RouterLink to="/app/admin/products" class="btn-muted">Cancel</RouterLink>
        <button class="btn-primary">Save</button>
      </div>
    </form>
  </div>

    </div>
  </div>
</template><script setup>
import AdminTopbar from '../../components/AdminTopbar.vue';
import AdminSidebar from '../../components/AdminSidebar.vue';



import axios from 'axios';
import { reactive, ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
const id = route.params.id;
const form = reactive({ name: '', slug: '', description: '', price_cents: 0, stock: 0, image_path: '', net_price_cents: 0, tax_cents: 0, selling_price_cents: 0 });
const errors = ref([]);

async function load() {
  const res = await axios.get(`/admin/products`, { headers: { Accept: 'application/json' }, params: { page: 1 } });
  const p = res.data.data.find(x => String(x.id) === String(id));
  if (p) Object.assign(form, p);
}

async function submit() {
  errors.value = [];
  try {
    await axios.post(`/admin/products/${id}?_method=PUT`, form);
    router.push('/app/admin/products');
  } catch (e) {
    const res = e.response?.data;
    if (res?.errors) errors.value = Object.values(res.errors).flat();
    else errors.value = [res?.message || 'Failed to update product'];
  }
}

onMounted(load);







function toggleTheme(){
  const root = document.documentElement;
  const isDark = root.classList.toggle('dark');
  try { localStorage.setItem('theme', isDark ? 'dark' : 'light'); } catch(e) {}
}
</script>
