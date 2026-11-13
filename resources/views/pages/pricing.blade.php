@extends('layouts.app')
@section('title', 'Simple and transparent pricing')
@section('content')
<div class="page-shell pricing-page">
    <div class="page-title centered"><span class="eyebrow">Plans for every workflow</span><h1>Simple &amp; Transparent Pricing</h1><p>Start for free. Upgrade when you need more room.</p></div>
    <div class="pricing-grid">
        <article class="price-card"><div class="price-top"><h2>Free</h2><p>For occasional comparisons</p></div><div class="price"><b>$0</b><span>/ forever</span></div><ul class="feature-list"><li>Basic code and text comparison</li><li>Side-by-side and unified views</li><li>Language selection</li><li>{{ config('codediff.free.comparisons_per_month') }} comparisons per month</li><li>Save up to {{ config('codediff.free.saved_comparisons') }} private diffs</li><li>Up to {{ number_format(config('codediff.free.characters_per_version')) }} characters</li></ul><a class="button button-outline full-button" href="{{ auth()->check() ? route('compare') : route('register') }}">Get started</a></article>
        <article class="price-card featured"><span class="popular-badge">Popular</span><div class="price-top"><h2>Pro</h2><p>For developers who compare every day</p></div><div class="price"><b>$9</b><span>/ month</span></div><ul class="feature-list"><li>Everything in Free</li><li>Higher usage and file limits</li><li>More private saved comparisons</li><li>Advanced exports</li><li>Priority support</li><li>New tools as they launch</li></ul><div class="coming-soon">Paid plans are coming soon</div><a class="button full-button" href="{{ route('lifetime.index') }}">Request Lifetime Pro</a></article>
        <article class="price-card lifetime-card"><div class="price-top"><h2><span class="crown">♛</span> Lifetime Pro</h2><p>One-time approval by our team</p></div><div class="price"><b>$0</b><span>/ lifetime</span></div><p class="approval-note">Admin approved</p><ul class="feature-list"><li>All Pro features available today</li><li>No recurring payments</li><li>Lifetime access while grant is active</li><li>Priority support</li><li>Request access with a short note</li></ul><a class="button button-outline full-button" href="{{ auth()->check() ? route('lifetime.index') : route('register') }}">Request Lifetime Access</a></article>
    </div>
    <p class="pricing-footnote">Lifetime access is granted by an administrator and can be revoked under the account terms. Paid subscriptions will be available in a later release.</p>
</div>
@endsection
