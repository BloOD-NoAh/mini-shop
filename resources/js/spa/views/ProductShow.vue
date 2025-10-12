<template>
  <div>
    <div v-if="product" class="card p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <img v-if="product.image_path" :src="asset(product.image_path)" :alt="product.name" class="w-full h-80 object-cover rounded" />
        <div v-else class="w-full h-80 bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400 rounded">No Image</div>
      </div>
      <div class="space-y-4">
        <h1 class="text-2xl font-semibold">{{ product.name }}</h1>
        <p class="text-lg">$ {{ formatPrice(product.price_cents) }}</p>
        <p class="text-gray-700 dark:text-gray-300">{{ product.description }}</p>
        <form @submit.prevent="addToCart">
          <label class="input-label">Quantity</label>
          <div class="flex items-center gap-2">
            <input v-model.number="quantity" type="number" min="1" class="w-24 input-field" />
            <button class="btn-primary">Add to Cart</button>
          </div>
        </form>
        <div v-if="flash" class="badge-success">{{ flash }}</div>
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


