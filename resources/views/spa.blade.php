<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Mini Shop') }}</title>
    @vite(['resources/css/app.css','resources/js/spa.js'])
  <script>(function(){try{const s=localStorage.getItem("theme");const p=window.matchMedia("(prefers-color-scheme: dark)").matches;if((s==="dark")||(!s&&p)){document.documentElement.classList.add("dark");}else{document.documentElement.classList.remove("dark");}}catch(e){}})();</script>\n</head>
<body class="layout-standard">
    <div id="app"></div>
</body>
</html>


