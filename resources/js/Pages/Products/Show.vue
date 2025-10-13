<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
  product: { type: Object, required: true },
});

const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
</script>

<template>
  <Head :title="product.name" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ product.name }}
      </h2>
    </template>

    <div class="py-6">
      <div class="container-narrow">
        <div class="card p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <img v-if="product.image_path" :src="product.image_path.startsWith('/') ? product.image_path : ('/' + product.image_path)" :alt="product.name" class="w-full h-80 object-cover rounded" />
            <div v-else class="w-full h-80 bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-400 rounded">No Image</div>
          </div>
          <div class="space-y-4">
            <h1 class="text-2xl font-semibold">{{ product.name }}</h1>
            <p class="text-lg">{{ product.price_formatted }}</p>
            <p class="text-gray-700 dark:text-gray-300">{{ product.description }}</p>

            <form :action="`/cart/add/${product.id}`" method="POST" class="space-y-3">
              <input type="hidden" name="_token" :value="csrf" />
              <div>
                <label class="input-label">Quantity</label>
                <input type="number" name="quantity" value="1" min="1" class="w-24 input-field" />
              </div>
              <button type="submit" class="btn-primary">Add to Cart</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

