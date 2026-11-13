@extends('layouts.app')
@section('title', 'Log in')
@section('content')
<div class="auth-page"><div class="auth-card"><span class="feature-icon purple">⌘</span><h1>Welcome back</h1><p>Log in to your CodeDiff workspace.</p><form method="POST" action="{{ route('login.store') }}" class="form-stack">@csrf<label>Email address</label><input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus><label>Password</label><input type="password" name="password" autocomplete="current-password" required><label class="check-control"><input type="checkbox" name="remember" value="1"><span></span> Remember me</label><button class="button full-button">Log in</button></form><div class="auth-links"><a href="{{ route('password.request') }}">Forgot password?</a><span>New here? <a href="{{ route('register') }}">Create an account</a></span></div></div></div>
@endsection
