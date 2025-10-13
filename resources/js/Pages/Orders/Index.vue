<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
  orders: { type: Array, default: () => [] },
});
</script>

<template>
  <Head title="Your Orders" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Your Orders</h2>
    </template>

    <div class="py-6">
      <div class="container-wide">
        <div class="bg-white dark:bg-gray-800 shadow rounded">
          <div class="divide-y divide-gray-200 dark:divide-gray-700">
            <div v-if="!orders.length" class="p-4 text-gray-600 dark:text-gray-300">No orders yet.</div>
            <div v-for="o in orders" :key="o.id" class="p-4 flex items-center justify-between">
              <div class="space-y-1">
                <div class="font-medium text-gray-900 dark:text-gray-100">Order #{{ o.id }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-300">Status: {{ o.status }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-300">Placed: {{ o.created_at }}</div>
              </div>
              <div class="flex items-center gap-4">
                <div class="font-semibold text-gray-900 dark:text-gray-100">{{ o.total_formatted }}</div>
                <a :href="`/orders/${o.id}`" class="text-indigo-600 hover:underline">View</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

