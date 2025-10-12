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
</head>
<body class="layout-standard">
    <nav class="topbar">
        <div class="container-wide h-16 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <a href="{{ url('/') }}" class="text-lg font-semibold">Mini Shop</a>
                <a href="{{ url('/cart') }}" class="text-gray-700 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white">Cart</a>
                @auth
                    @if (auth()->user()->is_admin ?? false)
                        <a href="{{ route('admin.dashboard') }}" class="text-gray-700 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white">Admin</a>
                    @endif
                @endauth
            </div>
            <div class="flex items-center gap-4">
                @auth
                    <span class="text-gray-700 dark:text-gray-200">{{ auth()->user()->name }}</span>
                    @if (Route::has('profile.edit'))
                        <a href="{{ route('profile.edit') }}" class="text-gray-700 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white">Profile</a>
                    @endif
                    @if (Route::has('logout'))
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn-muted">Logout</button>
                        </form>
                    @endif
                @else
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="text-gray-700 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white">Login</a>
                    @endif
                    @if (Route::has('admin.login'))
                        <a href="{{ route('admin.login') }}" class="text-gray-700 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white">Admin Login</a>
                    @endif
                @endauth
            </div>
        </div>
    </nav>

    @isset($header)
        <header class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800">
            <div class="container-wide py-4">
                {{ $header }}
            </div>
        </header>
    @endisset

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
