@extends('layouts.app')
@section('title', 'Reset password')
@section('content')
<div class="auth-page"><div class="auth-card"><span class="feature-icon purple">↻</span><h1>Reset your password</h1><p>Enter your account email and we’ll send a reset link.</p><form method="POST" action="{{ route('password.email') }}" class="form-stack">@csrf<label>Email address</label><input type="email" name="email" value="{{ old('email', $email ?? '') }}" autocomplete="email" required><button class="button full-button">Send reset link</button></form><div class="auth-links centered-links"><a href="{{ route('login') }}">Back to log in</a></div></div></div>
@endsection
