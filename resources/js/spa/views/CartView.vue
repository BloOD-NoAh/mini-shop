<template>
  <div>
    <h1 class="text-xl font-semibold mb-4">Your Cart</h1>
    <div v-if="flash" class="mb-4 p-4 rounded bg-green-100 text-green-800">{{ flash }}</div>
    <div class="bg-white shadow rounded p-4">
      <p v-if="items.length === 0" class="text-gray-600">Your cart is empty.</p>
      <div v-else class="divide-y">
        <div v-for="item in items" :key="item.id" class="py-4 flex items-center justify-between gap-4">
          <div class="flex items-center gap-4">
            <img v-if="item.product?.image_path" :src="asset(item.product.image_path)" class="w-16 h-16 object-cover rounded" />
            <div>
              <RouterLink :to="`/app/products/${item.product.slug}`" class="font-medium text-gray-900 hover:text-indigo-600">{{ item.product.name }}</RouterLink>
              <div class="text-gray-700">$ {{ formatPrice(item.product.price_cents) }}</div>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <form @submit.prevent="updateQty(item)">
              <div class="flex items-center gap-2">
                <input v-model.number="item.quantity" type="number" min="1" class="w-20 rounded border-gray-300" />
                <button class="px-3 py-2 bg-gray-800 text-white text-sm rounded hover:bg-gray-900">Update</button>
              </div>
            </form>
            <button @click="removeItem(item)" class="px-3 py-2 bg-red-600 text-white text-sm rounded hover:bg-red-700">Remove</button>
          </div>
        </div>
      </div>
      <div v-if="items.length" class="mt-6 flex items-center justify-between">
        <div class="text-lg font-semibold">Subtotal: $ {{ formatPrice(subtotal) }}</div>
        <button @click="checkout" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Checkout</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const items = ref([]);
const flash = ref('');

function asset(path) { return `/${path}`; }
function formatPrice(cents) { return (cents / 100).toFixed(2); }

const subtotal = computed(() => items.value.reduce((sum, i) => sum + i.product.price_cents * i.quantity, 0));

async function fetchCart() {
  const res = await axios.get('/cart');
  items.value = res.data.items || [];
}

async function updateQty(item) {
  await axios.post(`/cart/update/${item.product_id}`, { quantity: item.quantity });
  flash.value = 'Cart updated';
  setTimeout(() => (flash.value = ''), 1500);
}

async function removeItem(item) {
  await axios.delete(`/cart/remove/${item.product_id}`);
  await fetchCart();
  flash.value = 'Item removed';
  setTimeout(() => (flash.value = ''), 1500);
}

async function checkout() {
  // For SPA, use mock confirm endpoint directly
  try {
    const res = await axios.post('/checkout/confirm');
    const id = res.data.order.id;
    router.push(`/app/orders/${id}`);
  } catch (e) {
    flash.value = e.response?.data?.message || 'Checkout failed';
  }
}

onMounted(fetchCart);
</script>
