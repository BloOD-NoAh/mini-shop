<template>
  <div>
    <div v-if="product" class="bg-white shadow rounded p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <img v-if="product.image_path" :src="asset(product.image_path)" :alt="product.name" class="w-full h-80 object-cover rounded" />
        <div v-else class="w-full h-80 bg-gray-100 flex items-center justify-center text-gray-400 rounded">No Image</div>
      </div>
      <div class="space-y-4">
        <h1 class="text-2xl font-semibold text-gray-900">{{ product.name }}</h1>
        <p class="text-lg text-gray-800">$ {{ formatPrice(product.price_cents) }}</p>
        <p class="text-gray-700">{{ product.description }}</p>
        <form @submit.prevent="addToCart">
          <label class="block text-sm font-medium text-gray-700">Quantity</label>
          <div class="flex items-center gap-2">
            <input v-model.number="quantity" type="number" min="1" class="w-24 rounded border-gray-300" />
            <button class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Add to Cart</button>
          </div>
        </form>
        <div v-if="flash" class="p-3 bg-green-100 text-green-800 rounded">{{ flash }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios';
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
const product = ref(null);
const quantity = ref(1);
const flash = ref('');

function asset(path) { return `/${path}`; }
function formatPrice(cents) { return (cents / 100).toFixed(2); }

async function fetchProduct() {
  const res = await axios.get(`/products/${route.params.slug}`);
  product.value = res.data;
}

async function addToCart() {
  try {
    await axios.post(`/cart/add/${product.value.id}`, { quantity: quantity.value });
    flash.value = 'Added to cart';
    setTimeout(() => (flash.value = ''), 2000);
  } catch (e) {
    flash.value = e.response?.data?.message || 'Failed to add to cart';
  }
}

onMounted(fetchProduct);
</script>

