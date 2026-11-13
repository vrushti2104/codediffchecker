@extends('layouts.app')
@section('title', 'Choose a new password')
@section('content')
<div class="auth-page"><div class="auth-card"><span class="feature-icon purple">↻</span><h1>Choose a new password</h1><p>Use at least 10 characters for your new password.</p><form method="POST" action="{{ route('password.update') }}" class="form-stack">@csrf<input type="hidden" name="token" value="{{ $token }}"><label>Email address</label><input type="email" name="email" value="{{ old('email', $email) }}" required><label>New password</label><input type="password" name="password" autocomplete="new-password" minlength="10" required><label>Confirm password</label><input type="password" name="password_confirmation" autocomplete="new-password" required><button class="button full-button">Save new password</button></form></div></div>
@endsection
