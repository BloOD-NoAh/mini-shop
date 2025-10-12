<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Mini Shop') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-100">
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <a href="{{ url('/') }}" class="text-lg font-semibold text-gray-900">Mini Shop</a>
                <a href="{{ url('/cart') }}" class="text-gray-700 hover:text-gray-900">Cart</a>
                @auth
                    @if (auth()->user()->is_admin ?? false)
                        <a href="{{ route('admin.dashboard') }}" class="text-gray-700 hover:text-gray-900">Admin</a>
                    @endif
                @endauth
            </div>
            <div class="flex items-center gap-4">
                @auth
                    <span class="text-gray-700">{{ auth()->user()->name }}</span>
                    @if (Route::has('profile.edit'))
                        <a href="{{ route('profile.edit') }}" class="text-gray-700 hover:text-gray-900">Profile</a>
                    @endif
                    @if (Route::has('logout'))
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-gray-700 hover:text-gray-900">Logout</button>
                        </form>
                    @endif
                @else
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-gray-900">Login</a>
                    @endif
                    @if (Route::has('admin.login'))
                        <a href="{{ route('admin.login') }}" class="text-gray-700 hover:text-gray-900">Admin Login</a>
                    @endif
                @endauth
            </div>
        </div>
    </nav>

    @isset($header)
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
    @endisset

    <main>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
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
