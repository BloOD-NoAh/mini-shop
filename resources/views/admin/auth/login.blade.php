<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="/favicon.ico">
    <style> body { background-color: #f8fafc } </style>
    @vite('resources/js/app.js')
    @vite('resources/css/app.css')
    @vite('resources/js/bootstrap.js')
    {{-- Keep Vite includes consistent with app layout --}}
</head>
<body class="min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md bg-white shadow rounded p-6">
        <h1 class="text-2xl font-semibold mb-2">Admin Login</h1>
        <p class="text-sm text-gray-600 mb-4">Sign in to the admin dashboard</p>

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
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input id="email" name="email" type="email" required autofocus
                       class="mt-1 block w-full rounded border-gray-300" value="{{ old('email') }}">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input id="password" name="password" type="password" required
                       class="mt-1 block w-full rounded border-gray-300">
            </div>
            <div class="flex items-center justify-between">
                <a href="{{ url('/') }}" class="text-sm text-gray-600 hover:text-gray-900">Back to shop</a>
                <button class="px-4 py-2 rounded bg-gray-900 text-white hover:bg-gray-800" type="submit">Sign in</button>
            </div>
        </form>
    </div>
</body>
</html>

