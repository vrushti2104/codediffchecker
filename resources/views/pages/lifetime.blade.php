@extends('layouts.app')
@section('title', 'Request Lifetime Pro')
@section('content')
<div class="page-shell app-layout">
    <aside class="app-sidebar"><a class="side-link" href="{{ route('dashboard') }}"><span>⌂</span> Dashboard</a><a class="side-link" href="{{ route('compare') }}"><span>⌘</span> New Comparison</a><a class="side-link" href="{{ route('comparisons.index') }}"><span>▤</span> Saved Diffs</a><a class="side-link" href="{{ route('pricing') }}"><span>◈</span> Subscription</a><a class="side-link active" href="{{ route('lifetime.index') }}"><span>♛</span> Lifetime Request</a><a class="side-link" href="{{ route('dashboard') }}#account"><span>⚙</span> Account</a></aside>
    <div class="app-content narrow-content"><div class="dashboard-heading"><div><span class="eyebrow">A one-time access request</span><h1>Request Free Lifetime Access</h1><p>Tell us a little about how you use CodeDiff.</p></div></div>
        <div class="content-card request-card"><div class="request-intro"><span class="feature-icon purple">♛</span><div><h2>Lifetime Pro</h2><p>Selected users can receive ongoing access to available Pro features without recurring billing. Every request is reviewed by our team.</p></div></div>
            @if($active)<div class="status-card approved"><span class="status-icon">✓</span><div><b>Lifetime Pro is active</b><p>Your account currently has Lifetime Pro access.</p></div><span class="status-pill">Active</span></div>
            @elseif($latestRequest && $latestRequest->status === 'pending')<div class="status-card pending"><span class="status-icon">◷</span><div><b>Your request is under review</b><p>Submitted {{ $latestRequest->created_at->format('M j, Y') }}. We’ll notify you after a decision.</p><blockquote>{{ $latestRequest->reason }}</blockquote></div><span class="status-pill">Pending review</span></div>
            @elseif($latestRequest && $latestRequest->status === 'rejected')<div class="status-card rejected"><span class="status-icon">↗</span><div><b>Previous request reviewed</b><p>Reviewed {{ optional($latestRequest->reviewed_at)->format('M j, Y') }} · {{ $latestRequest->review_note ?: 'You can submit a new request below.' }}</p></div><span class="status-pill">Not approved</span></div>
            @endif
            @if(!$active && (!$latestRequest || $latestRequest->status !== 'pending'))<form method="POST" action="{{ route('lifetime.store') }}" class="form-stack lifetime-form">@csrf<label for="reason">Reason for Request <span class="required">*</span></label><textarea id="reason" name="reason" rows="5" minlength="20" maxlength="2000" placeholder="I’m a student, open-source contributor, or long-term user…" required>{{ old('reason') }}</textarea><small>Share how Lifetime Pro would help your work. A minimum of 20 characters.</small><button class="button" type="submit">Submit request <span>→</span></button></form>@endif
        </div>
        <p class="fine-print">Lifetime means the access has no scheduled expiration while the grant remains active. Administrators can revoke a grant under the account terms; decisions are recorded.</p>
    </div>
</div>
@endsection
