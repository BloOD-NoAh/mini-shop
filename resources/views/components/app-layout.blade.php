<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Mini Shop') }}</title>
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
    <script>
      function toggleTheme(){
        const root = document.documentElement;
        const isDark = root.classList.toggle("dark");
        try { localStorage.setItem("theme", isDark ? "dark" : "light"); } catch(e) {}
      }
    </script>
</head>
<body class="layout-standard">
    <nav class="topbar">
        <div class="container-wide h-16 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <a href="{{ url('/') }}"
                   @class([
                     'text-lg font-semibold',
                     'text-gray-900 dark:text-white' => request()->routeIs('home'),
                     'text-gray-800 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white' => !request()->routeIs('home'),
                   ])
                   @if(request()->routeIs('home')) aria-current="page" @endif
                >Mini Shop</a>
                <a href="{{ url('/cart') }}"
                   @class([
                     'hover:text-gray-900 dark:hover:text-white',
                     'text-gray-900 dark:text-white font-semibold' => request()->is('cart*'),
                     'text-gray-700 dark:text-gray-200' => !request()->is('cart*'),
                   ])
                   @if(request()->is('cart*')) aria-current="page" @endif
                >Cart</a>
                @auth
                    @if (auth()->user()->is_admin ?? false)
                        <a href="{{ route('admin.dashboard') }}"
                           @class([
                             'hover:text-gray-900 dark:hover:text-white',
                             'text-gray-900 dark:text-white font-semibold' => request()->is('admin*'),
                             'text-gray-700 dark:text-gray-200' => !request()->is('admin*'),
                           ])
                           @if(request()->is('admin*')) aria-current="page" @endif
                        >Admin</a>
                    @endif
                @endauth
            </div>
            <div class="flex items-center gap-4">
                <button type="button" onclick="toggleTheme()" class="btn-muted" title="Toggle theme">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0112 21.75a9.75 9.75 0 010-19.5 9.718 9.718 0 019.752 6.748 7.5 7.5 0 00-.001 6.004z"/></svg>
                </button>
                @auth
                    <span class="text-gray-700 dark:text-gray-200">{{ auth()->user()->name }}</span>
                    @if (Route::has('profile.edit'))
                        <a href="{{ route('profile.edit') }}"
                           @class([
                             'hover:text-gray-900 dark:hover:text-white',
                             'text-gray-900 dark:text-white font-semibold' => request()->routeIs('profile.edit'),
                             'text-gray-700 dark:text-gray-200' => !request()->routeIs('profile.edit'),
                           ])
                           @if(request()->routeIs('profile.edit')) aria-current="page" @endif
                        >Profile</a>
                    @endif
                    @if (Route::has('logout'))
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn-muted">Logout</button>
                        </form>
                    @endif
                @else
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}"
                           @class([
                             'hover:text-gray-900 dark:hover:text-white',
                             'text-gray-900 dark:text-white font-semibold' => request()->routeIs('login'),
                             'text-gray-700 dark:text-gray-200' => !request()->routeIs('login'),
                           ])
                           @if(request()->routeIs('login')) aria-current="page" @endif
                        >Login</a>
                    @endif
                    @if (Route::has('admin.login'))
                        <a href="{{ route('admin.login') }}"
                           @class([
                             'hover:text-gray-900 dark:hover:text-white',
                             'text-gray-900 dark:text-white font-semibold' => request()->routeIs('admin.login'),
                             'text-gray-700 dark:text-gray-200' => !request()->routeIs('admin.login'),
                           ])
                           @if(request()->routeIs('admin.login')) aria-current="page" @endif
                        >Admin Login</a>
                    @endif
                @endauth
            </div>
        </div>
    </nav>

    

    <main>
        <div class="container-wide mt-4">
            @if (session('status'))
                <div class="mb-4 p-4 rounded bg-green-100 text-green-800">
                    {{ session('status') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 p-4 rounded bg-red-100 text-red-800">
                    <ul class="list-disc ml-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
        {{ $slot }}
    </main>
</body>
</html>

