<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CodeDiff') · CodeDiff</title>
    <meta name="description" content="Compare code and text with a fast, private diff workspace.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="{{ request()->is('admin*') ? 'admin-body' : '' }}">
<header class="topbar">
    <a class="brand" href="{{ route('home') }}" aria-label="CodeDiff home"><span class="brand-mark">‹›</span><span>CodeDiff</span></a>
    <button class="menu-toggle" type="button" aria-label="Toggle navigation" data-menu-toggle>☰</button>
    <nav class="topnav" data-menu>
        <a class="{{ request()->routeIs('compare*') ? 'active' : '' }}" href="{{ route('compare') }}">Compare</a>
        <a class="{{ request()->routeIs('features') ? 'active' : '' }}" href="{{ route('features') }}">Features</a>
        <a class="{{ request()->routeIs('pricing') ? 'active' : '' }}" href="{{ route('pricing') }}">Pricing</a>
        @auth
            <a href="{{ route('comparisons.index') }}">My Comparisons</a>
        @else
            <a href="{{ route('features') }}#how-it-works">Docs</a>
        @endauth
    </nav>
    <div class="top-actions">
        @auth
            @if(auth()->user()->is_admin)<a class="top-admin" href="{{ route('admin.dashboard') }}">Admin</a>@endif
            <a class="avatar" href="{{ route('dashboard') }}" title="Dashboard">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</a>
            <a class="user-name" href="{{ route('dashboard') }}">{{ auth()->user()->name }}</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="link-button" type="submit">Log out</button></form>
        @else
            <a class="login-link" href="{{ route('login') }}">Log in</a>
            <a class="button button-small" href="{{ route('register') }}">Sign up</a>
        @endauth
    </div>
</header>
@if(session('status') || $errors->any())
    <div class="flash-wrap">
        @if(session('status'))<div class="flash success">{{ session('status') }}</div>@endif
        @if($errors->any())<div class="flash error">{{ $errors->first() }}</div>@endif
    </div>
@endif
<main>@yield('content')</main>
<footer class="site-footer"><a class="brand footer-brand" href="{{ route('home') }}"><span class="brand-mark">‹›</span><span>CodeDiff</span></a><span>Private by default. Built for careful comparisons.</span><div><a href="{{ route('features') }}">Features</a><a href="{{ route('pricing') }}">Pricing</a><a href="{{ route('privacy') }}">Privacy</a><a href="{{ route('terms') }}">Terms</a></div></footer>
</body>
</html>
