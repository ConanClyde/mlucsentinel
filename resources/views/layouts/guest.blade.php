<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MLUC Sentinel')</title>
    
    <!-- PWA Meta Tags -->
    <meta name="description" content="MLUC Parking and Reporting Management System - Vehicle registration, sticker management, and campus security reporting">
    <meta name="theme-color" content="#1b1b18" media="(prefers-color-scheme: dark)">
    <meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
    <link rel="manifest" href="/manifest.json">
    
    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" sizes="180x180" href="/images/icons/icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/icons/icon-192x192.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/images/icons/icon-192x192.png">
    
    <!-- Mobile Web App -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="MLUC Sentinel">
    
    <!-- Microsoft Tiles -->
    <meta name="msapplication-TileColor" content="#1b1b18">
    <meta name="msapplication-TileImage" content="/images/icons/icon-144x144.png">
    
    <!-- Fontshare - Satoshi -->
    <link href="https://api.fontshare.com/v2/css?f[]=satoshi@400,500,700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- PWA Scripts -->
    <script src="/pwa-register.js" defer></script>
    
    <!-- Dark Mode Script - Run before page renders to prevent flash -->
    <script>
        // Check for saved theme preference or default to light mode
        const savedTheme = localStorage.getItem('theme') || 'light';
        if (savedTheme === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>
</head>
<body class="bg-[#FDFDFC] dark:bg-[#161615]">
    @yield('content')
</body>
</html>
