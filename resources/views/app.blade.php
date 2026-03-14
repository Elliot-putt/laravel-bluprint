<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel Blueprint') }}</title>
        <meta name="description" content="{{ config('app.name', 'Laravel Blueprint') }} — a clean Laravel + Vue 3 + Inertia.js starter template.">
        <meta name="keywords" content="laravel, vue3, inertia, starter, template">
        <meta name="author" content="{{ config('app.name', 'Laravel Blueprint') }}">
        <meta name="robots" content="index, follow">
        <link rel="canonical" href="{{ url()->current() }}">

        <!-- Open Graph / Facebook -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:title" content="{{ config('app.name', 'Laravel Blueprint') }}">
        <meta property="og:description" content="{{ config('app.name', 'Laravel Blueprint') }} — a clean Laravel + Vue 3 + Inertia.js starter template.">
        <meta property="og:image" content="{{ asset('images/og-image.png') }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta property="og:image:alt" content="{{ config('app.name', 'Laravel Blueprint') }} Screenshot">
        <meta property="og:site_name" content="{{ config('app.name', 'Laravel Blueprint') }}">
        <meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:url" content="{{ url()->current() }}">
        <meta name="twitter:title" content="{{ config('app.name', 'Laravel Blueprint') }}">
        <meta name="twitter:description" content="{{ config('app.name', 'Laravel Blueprint') }} — a clean Laravel + Vue 3 + Inertia.js starter template.">
        <meta name="twitter:image" content="{{ asset('images/og-image.png') }}">
        <meta name="twitter:image:alt" content="{{ config('app.name', 'Laravel Blueprint') }} Screenshot">

        <!-- Additional SEO -->
        <meta name="theme-color" content="#000000">
        <meta name="msapplication-TileColor" content="#000000">
        <meta name="application-name" content="{{ config('app.name', 'Laravel Blueprint') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        @routes
        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
