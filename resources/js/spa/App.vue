<template>
  <div>
    <nav class="bg-white border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <div class="flex items-center gap-6">
          <RouterLink to="/app" class="text-lg font-semibold text-gray-900">Mini Shop</RouterLink>
          <RouterLink to="/app/cart" class="text-gray-700 hover:text-gray-900">Cart</RouterLink>
          <RouterLink to="/app/admin/products" class="text-gray-700 hover:text-gray-900">Admin</RouterLink>
        </div>
      </div>
    </nav>
    <main class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <RouterView />
      </div>
    </main>
  </div>
</template>

<script setup>
// simple root shell
</script>

