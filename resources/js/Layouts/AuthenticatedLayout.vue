<script setup>
import { ref, onMounted, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import GlobalAiSupportModal from '@/Components/GlobalAiSupportModal.vue';

const showingNavigationDropdown = ref(false);
const page = usePage();
const isAuthed = computed(() => !!(page?.props?.auth && page.props.auth.user));
const globalChatOpen = ref(false);

function toggleTheme() {
    const root = document.documentElement;
    const isDark = root.classList.toggle('dark');
    try { localStorage.setItem('theme', isDark ? 'dark' : 'light'); } catch(e) {}
}

onMounted(() => {
    try {
        const saved = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const shouldDark = saved ? saved === 'dark' : prefersDark;
        const root = document.documentElement;
        if (shouldDark) root.classList.add('dark'); else root.classList.remove('dark');
    } catch (e) {}
});

function openAiSupport() {
    if (!isAuthed.value) return;
    const hasInline = !!document.querySelector('[data-ai-support-present]');
    if (hasInline) {
        window.dispatchEvent(new CustomEvent('open-ai-support'));
    } else {
        globalChatOpen.value = true;
    }
}
</script>

<template>
    <div>
        <div class="min-h-screen bg-[rgb(46_95_50_/_10%)] dark:bg-gray-900">
            <nav
                class="sticky top-0 z-20 border-b border-gray-100 bg-white/90 backdrop-blur dark:border-gray-700 dark:bg-gray-800/80"
            >
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('home')">
                                    <ApplicationLogo
                                        class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200"
                                    />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink :href="route('home')" :active="route().current('home')">Home</NavLink>
                                <NavLink :href="route('orders.index')" :active="route().current('orders.index')">Orders</NavLink>
                                <NavLink href="/cart" :active="$page.url && $page.url.startsWith('/cart')">Cart</NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center gap-3">
                            <!-- Dark mode toggle -->
                            <button
                                type="button"
                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none dark:bg-gray-800 dark:text-gray-400 dark:hover:text-gray-300"
                                @click="toggleTheme"
                                title="Toggle theme"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0112 21.75a9.75 9.75 0 010-19.5 9.718 9.718 0 019.752 6.748 7.5 7.5 0 00-.001 6.004z"/></svg>
                            </button>

                            <!-- Settings Dropdown (auth only) -->
                            <div class="relative ms-3" v-if="$page.props.auth && $page.props.auth.user">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none dark:bg-gray-800 dark:text-gray-400 dark:hover:text-gray-300"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                                        <DropdownLink :href="route('addresses.index')">Addresses</DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">Log Out</DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                            <div v-else>
                                <Link :href="route('login')" class="text-gray-700 dark:text-gray-300 hover:underline">Login</Link>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none dark:text-gray-500 dark:hover:bg-gray-900 dark:hover:text-gray-400 dark:focus:bg-gray-900 dark:focus:text-gray-400"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <div class="px-3 pb-2">
                            <button
                                type="button"
                                class="w-full inline-flex items-center justify-center rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                                @click="toggleTheme"
                            >
                                Toggle theme
                            </button>
                        </div>
                        <ResponsiveNavLink href="/cart" :active="$page.url && $page.url.startsWith('/cart')">Cart</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('orders.index')" :active="route().current('orders.index')">Orders</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('home')" :active="route().current('home')">Home</ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div
                        class="border-t border-gray-200 pb-1 pt-4 dark:border-gray-600"
                    >
                        <template v-if="$page.props.auth && $page.props.auth.user">
                            <div class="px-4">
                                <div class="text-base font-medium text-gray-800 dark:text-gray-200">
                                    {{ $page.props.auth.user.name }}
                                </div>
                                <div class="text-sm font-medium text-gray-500">
                                    {{ $page.props.auth.user.email }}
                                </div>
                            </div>

                            <div class="mt-3 space-y-1">
                                <ResponsiveNavLink :href="route('profile.edit')">Profile</ResponsiveNavLink>
                                <ResponsiveNavLink :href="route('addresses.index')">Addresses</ResponsiveNavLink>
                                <ResponsiveNavLink :href="route('logout')" method="post" as="button">Log Out</ResponsiveNavLink>
                            </div>
                        </template>
                        <template v-else>
                            <div class="mt-2 space-y-1">
                                <ResponsiveNavLink :href="route('login')">Login</ResponsiveNavLink>
                            </div>
                        </template>
                    </div>
                </div>
            </nav>

            

            <!-- Page Content -->
            <main>
                <slot />
            </main>

            <!-- Global AI Support Launcher (auth only) -->
            <button
                v-if="isAuthed"
                @click="openAiSupport"
                class="fixed bottom-6 right-6 z-30 rounded-full shadow-lg bg-indigo-600 text-white w-12 h-12 flex items-center justify-center"
                title="Customer Support"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 8h7M7 12h5M5 20l4-4h8a2 2 0 002-2V6a2 2 0 00-2-2H7a2 2 0 00-2 2v12z"/></svg>
            </button>

            <GlobalAiSupportModal v-if="isAuthed" v-model="globalChatOpen" />
        </div>
    </div>
</template>
