<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Mini Shop') }} — Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
<body class="min-h-screen bg-gray-100" x-data x-init="(() => { try { if(localStorage.getItem('admin_sidebar_collapsed')==='1'){ document.body.classList.add('sidebar-collapsed') } } catch(e){} })()">
    <script>
        function toggleAdminSidebar(){
            document.body.classList.toggle('sidebar-collapsed');
            try { localStorage.setItem('admin_sidebar_collapsed', document.body.classList.contains('sidebar-collapsed') ? '1':'0'); } catch(e){}
        }
    </script>
    <aside class="admin-sidebar fixed inset-y-0 left-0 bg-white border-r border-gray-200 z-40">
        <div class="h-16 flex items-center px-4 border-b">
            <a href="{{ route('admin.dashboard') }}" class="text-lg font-semibold text-gray-900">Admin</a>
        </div>
        <nav class="p-2 space-y-1">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-100 font-medium' : '' }}">
                <span>🏠</span>
                <span class="label">Dashboard</span>
            </a>
            <a href="{{ route('admins.index') }}" class="flex items-center gap-3 px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admins.*') ? 'bg-gray-100 font-medium' : '' }}">
                <span>👤</span>
                <span class="label">Admins</span>
            </a>
            <a href="{{ url('/admin/products') }}" class="flex items-center gap-3 px-3 py-2 rounded hover:bg-gray-100 {{ request()->is('admin/products*') ? 'bg-gray-100 font-medium' : '' }}">
                <span>📦</span>
                <span class="label">Products</span>
            </a>
            <a href="{{ route('admin.sales') }}" class="flex items-center gap-3 px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.sales') ? 'bg-gray-100 font-medium' : '' }}">
                <span>💰</span>
                <span class="label">Sales</span>
            </a>
            <a href="{{ route('admin.customers') }}" class="flex items-center gap-3 px-3 py-2 rounded hover:bg-gray-100 {{ request()->routeIs('admin.customers') ? 'bg-gray-100 font-medium' : '' }}">
                <span>🧑‍🤝‍🧑</span>
                <span class="label">Customers</span>
            </a>
        </nav>
    </aside>

    <div class="admin-main min-h-screen">
        <nav class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-4 lg:px-6">
            <div class="flex items-center gap-3">
                <button type="button" onclick="toggleAdminSidebar()" class="px-2 py-1 rounded border border-gray-300 hover:bg-gray-50" title="Toggle sidebar">☰</button>
                <a href="{{ route('home') }}" class="text-gray-700 hover:text-gray-900">Back to shop</a>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-gray-700">{{ auth()->user()->name ?? '' }}</span>
                <a href="{{ route('profile.edit') }}" class="text-gray-700 hover:text-gray-900">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-gray-700 hover:text-gray-900">Logout</button>
                </form>
            </div>
        </nav>

        @isset($header)
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <main class="p-4 lg:p-6">
            {{ $slot }}
        </main>
    </div>
</body>
</html>
