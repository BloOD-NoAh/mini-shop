<template>
  <nav class="topbar">
    <div class="container-wide h-16 flex items-center justify-between">
      <div class="flex items-center gap-6">
        <RouterLink to="/app/admin/products" class="text-lg font-semibold">Admin</RouterLink>
        <RouterLink to="/app" class="text-gray-700 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white">Shop</RouterLink>
      </div>
      <div>
        <button @click="$emit('toggle-theme')" class="btn-muted" title="Toggle theme">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0112 21.75a9.75 9.75 0 010-19.5 9.718 9.718 0 019.752 6.748 7.5 7.5 0 00-.001 6.004z"/></svg>
        </button>
      </div>
    </div>
  </nav>
</template>
<script setup>
</script>
