<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Mini Shop') }} - Admin</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
    <script>
      (function() {
        try {
          const saved = localStorage.getItem('theme');
          const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
          if ((saved === 'dark') || (!saved && prefersDark)) {
            document.documentElement.classList.add('dark');
          }
        } catch (e) {}
      })();
    </script>
    <style>
      .sidebar-collapsed .admin-sidebar { width: 4rem; }
      .sidebar-collapsed .admin-sidebar .label { display:none; }
      .sidebar-collapsed .admin-main { margin-left: 4rem; }
      @media (min-width: 1024px) {
        .admin-sidebar { width: 16rem; }
        .admin-main { margin-left: 16rem; }
      }
    </style>
</head>
<body class="layout-standard" x-data x-init="(() => { try { if(localStorage.getItem('admin_sidebar_collapsed')==='1'){ document.body.classList.add('sidebar-collapsed') } } catch(e){} })()">
    <script>
      function toggleAdminSidebar(){
        document.body.classList.toggle('sidebar-collapsed');
        try { localStorage.setItem('admin_sidebar_collapsed', document.body.classList.contains('sidebar-collapsed') ? '1':'0'); } catch(e){}
      }
      function toggleTheme(){
        const root = document.documentElement;
        const isDark = root.classList.toggle('dark');
        try { localStorage.setItem('theme', isDark ? 'dark' : 'light'); } catch(e){}
      }
    </script>
    <aside class="admin-sidebar fixed inset-y-0 left-0 bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 z-40">
        <div class="h-16 flex items-center px-4 border-b border-gray-200 dark:border-gray-800 topbar">
            <a href="{{ route('admin.dashboard') }}" class="text-lg font-semibold text-gray-900 dark:text-gray-100">Admin</a>
        </div>
        <nav class="p-2 space-y-1">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-100 dark:bg-gray-800 font-medium' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M4.5 10.5V21h15V10.5"/></svg>
                <span class="label">Dashboard</span>
            </a>
            <a href="{{ route('admins.index') }}" class="flex items-center gap-3 px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800 {{ request()->routeIs('admins.*') ? 'bg-gray-100 dark:bg-gray-800 font-medium' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19a4 4 0 10-6 0M12 3a4 4 0 110 8 4 4 0 010-8z"/></svg>
                <span class="label">Admins</span>
            </a>
            <a href="{{ url('/admin/products') }}" class="flex items-center gap-3 px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800 {{ request()->is('admin/products*') ? 'bg-gray-100 dark:bg-gray-800 font-medium' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l1.5 8.25A2.25 2.25 0 006 22.5h12a2.25 2.25 0 002.25-2.25L21.75 12M2.25 12h19.5M2.25 12L6 3h12l3.75 9"/></svg>
                <span class="label">Products</span>
            </a>
            <a href="{{ route('admin.sales') }}" class="flex items-center gap-3 px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800 {{ request()->routeIs('admin.sales') ? 'bg-gray-100 dark:bg-gray-800 font-medium' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18M7 13l3 3 7-7"/></svg>
                <span class="label">Sales</span>
            </a>
            <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800 {{ request()->routeIs('admin.orders.*') ? 'bg-gray-100 dark:bg-gray-800 font-medium' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 12h18M3 17h18"/></svg>
                <span class="label">Orders</span>
            </a>
            <a href="{{ route('admin.customers') }}" class="flex items-center gap-3 px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800 {{ request()->routeIs('admin.customers') ? 'bg-gray-100 dark:bg-gray-800 font-medium' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20a4 4 0 10-8 0M12 4a4 4 0 110 8 4 4 0 010-8zM19 8a3 3 0 110 6M5 14a3 3 0 110-6"/></svg>
                <span class="label">Customers</span>
            </a>
            <a href="{{ route('admin.ai') }}" class="flex items-center gap-3 px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800 {{ request()->routeIs('admin.ai') ? 'bg-gray-100 dark:bg-gray-800 font-medium' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h6M5 20l4-4h10a2 2 0 002-2V6a2 2 0 00-2-2H7a2 2 0 00-2 2v12z"/></svg>
                <span class="label">AI Settings</span>
            </a>
        </nav>
    </aside>

    <div class="admin-main min-h-screen">
        <nav class="topbar h-16 flex items-center justify-between px-4 lg:px-6">
            <div class="flex items-center gap-3">
                <button type="button" onclick="toggleAdminSidebar()" class="btn-muted" title="Toggle sidebar">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/></svg>
                </button>
                <a href="{{ route('home') }}" class="text-gray-700 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white">Back to shop</a>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" onclick="toggleTheme()" class="btn-muted" title="Toggle theme">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0112 21.75a9.75 9.75 0 010-19.5 9.718 9.718 0 019.752 6.748 7.5 7.5 0 00-.001 6.004z"/></svg>
                </button>
                <span class="text-gray-700 dark:text-gray-200">{{ auth()->user()->name ?? '' }}</span>
                <a href="{{ route('profile.edit') }}" class="text-gray-700 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-muted">Logout</button>
                </form>
            </div>
        </nav>

        @isset($header)
            <header class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800">
                <div class="container-wide py-4">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <main class="content">
            {{ $slot }}
        </main>
    </div>
</body>
</html>
