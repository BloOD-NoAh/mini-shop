<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="/favicon.ico">
    <style></style>
    @vite('resources/js/app.js')
    @vite('resources/css/app.css')
    @vite('resources/js/bootstrap.js')
    {{-- Keep Vite includes consistent with app layout --}}
</head>
<body class="layout-standard flex items-center justify-center">
    <div class="w-full max-w-md card">
        <h1 class="text-2xl font-semibold mb-2">Admin Login</h1>
        <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">Sign in to the admin dashboard</p>

        @if ($errors->any())
            <div class="mb-4 p-3 rounded bg-red-100 text-red-800">
                <ul class="list-disc ml-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.login.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="input-label">Email</label>
                <input id="email" name="email" type="email" required autofocus
                       class="input-field" value="{{ old('email') }}">
            </div>
            <div>
                <label for="password" class="input-label">Password</label>
                <input id="password" name="password" type="password" required
                       class="input-field">
            </div>
            <div class="flex items-center justify-between">
                <a href="{{ url('/') }}" class="text-sm text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">Back to shop</a>
                <button class="btn-primary" type="submit">Sign in</button>
            </div>
        </form>
    </div>
</body>
</html>


