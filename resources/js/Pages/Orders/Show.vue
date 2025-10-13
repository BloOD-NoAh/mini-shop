<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
  order: { type: Object, required: true },
});
</script>

<template>
  <Head :title="`Order #${order.id}`" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Order #{{ order.id }}</h2>
    </template>

    <div class="py-6">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 shadow rounded p-6 space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <div class="text-gray-700 dark:text-gray-300">Status</div>
              <div class="font-medium text-gray-900 dark:text-gray-100">{{ (order.status || '').charAt(0).toUpperCase() + (order.status || '').slice(1) }}</div>
            </div>
            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
              {{ order.total_formatted }}
            </div>
          </div>

          <div v-if="order.shipping_address" class="mt-2 p-3 rounded bg-gray-50 dark:bg-gray-900/50">
            <div class="text-gray-700 dark:text-gray-300 mb-1">Shipping Address</div>
            <div class="text-gray-900 dark:text-gray-100">
              {{ order.shipping_address.full_name }}<br>
              {{ order.shipping_address.line1 }}<br>
              <template v-if="order.shipping_address.line2">
                {{ order.shipping_address.line2 }}<br>
              </template>
              {{ order.shipping_address.city }}<template v-if="order.shipping_address.state">, {{ order.shipping_address.state }}</template> {{ order.shipping_address.postal_code }}<br>
              {{ order.shipping_address.country }}
              <template v-if="order.shipping_address.phone">
                <br>Phone: {{ order.shipping_address.phone }}
              </template>
            </div>
          </div>

          <div class="divide-y divide-gray-200 dark:divide-gray-700">
            <div v-for="i in order.items" :key="i.id" class="py-3 flex items-center justify-between">
              <div class="flex items-center gap-3">
                <img v-if="i.product && i.product.image_path" :src="i.product.image_path.startsWith('/') ? i.product.image_path : ('/' + i.product.image_path)" :alt="i.product?.name || 'Product'" class="w-12 h-12 object-cover rounded">
                <div>
                  <div class="font-medium text-gray-900 dark:text-gray-100">{{ i.product?.name || 'Product' }}</div>
                  <div class="text-gray-700 dark:text-gray-300">Qty: {{ i.quantity }}</div>
                </div>
              </div>
              <div class="text-gray-900 dark:text-gray-100">{{ i.unit_price_formatted }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

