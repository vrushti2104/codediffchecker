@extends('layouts.app')
@section('title', 'Verify your email')
@section('content')
<div class="auth-page"><div class="auth-card"><span class="feature-icon purple">✉</span><h1>Check your inbox</h1><p>Verify your email address to unlock your dashboard and submit a Lifetime Pro request.</p><form method="POST" action="{{ route('verification.send') }}" class="form-stack">@csrf<button class="button full-button">Resend verification email</button></form><div class="auth-links centered-links"><a href="{{ route('compare') }}">Continue as guest</a></div></div></div>
@endsection
