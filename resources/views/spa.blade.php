<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Mini Shop') }}</title>
    @vite(['resources/css/app.css','resources/js/spa.js'])
</head>
<body class="min-h-screen bg-gray-100">
    <div id="app"></div>
</body>
</html>

