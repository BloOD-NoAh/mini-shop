<template>
  <div>
    <h1 class="text-xl font-semibold mb-4">Edit Product #{{ id }}</h1>
    <div v-if="errors.length" class="mb-4 p-4 rounded bg-red-100 text-red-800">
      <ul class="list-disc ml-5"><li v-for="(e,i) in errors" :key="i">{{ e }}</li></ul>
    </div>
    <form @submit.prevent="submit" class="bg-white shadow rounded p-6 space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700">Name</label>
        <input v-model="form.name" class="mt-1 w-full rounded border-gray-300" required />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Slug</label>
        <input v-model="form.slug" class="mt-1 w-full rounded border-gray-300" required />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Description</label>
        <textarea v-model="form.description" class="mt-1 w-full rounded border-gray-300" rows="4" />
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Price (cents)</label>
          <input type="number" v-model.number="form.price_cents" min="0" class="mt-1 w-full rounded border-gray-300" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Stock</label>
          <input type="number" v-model.number="form.stock" min="0" class="mt-1 w-full rounded border-gray-300" required />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Image Path</label>
          <input v-model="form.image_path" class="mt-1 w-full rounded border-gray-300" />
        </div>
      </div>
      <div class="flex items-center justify-end gap-2">
        <RouterLink to="/app/admin/products" class="px-4 py-2 bg-gray-100 text-gray-700 rounded">Cancel</RouterLink>
        <button class="px-4 py-2 bg-indigo-600 text-white rounded">Save</button>
      </div>
    </form>
  </div>
</template>

<script setup>
import axios from 'axios';
import { reactive, ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
const id = route.params.id;
const form = reactive({ name: '', slug: '', description: '', price_cents: 0, stock: 0, image_path: '' });
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
</script>

