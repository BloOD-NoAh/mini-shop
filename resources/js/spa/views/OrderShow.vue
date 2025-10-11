<template>
  <div>
    <h1 class="text-xl font-semibold mb-4">Order #{{ order?.id }}</h1>
    <div v-if="order" class="bg-white shadow rounded p-6 space-y-4">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-gray-700">Status</div>
          <div class="font-medium text-gray-900">{{ (order.status || '').toUpperCase() }}</div>
        </div>
        <div class="text-lg font-semibold">Total: $ {{ formatPrice(order.total_cents) }}</div>
      </div>
      <div class="divide-y">
        <div v-for="it in order.items" :key="it.id" class="py-3 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <img v-if="it.product?.image_path" :src="asset(it.product.image_path)" class="w-12 h-12 object-cover rounded" />
            <div>
              <div class="font-medium text-gray-900">{{ it.product?.name || 'Product' }}</div>
              <div class="text-gray-700">Qty: {{ it.quantity }}</div>
            </div>
          </div>
          <div class="text-gray-900">$ {{ formatPrice(it.unit_price_cents) }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios';
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
const order = ref(null);

function asset(p) { return `/${p}`; }
function formatPrice(c) { return (c / 100).toFixed(2); }

async function fetchOrder() {
  const res = await axios.get(`/orders/${route.params.id}`);
  order.value = res.data;
}

onMounted(fetchOrder);
</script>

