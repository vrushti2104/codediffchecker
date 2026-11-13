@extends('layouts.app')
@section('title', 'Features')
@section('content')
<div class="page-shell features-page">
    <div class="page-title"><span class="eyebrow">Built around the review</span><h1>Everything you need to see what changed.</h1><p>CodeDiff keeps the comparison workflow direct, readable, and private.</p></div>
    <div class="feature-detail-grid">
        <article class="feature-detail"><span class="feature-icon purple">⌘</span><h2>Code and text diff</h2><p>Compare pasted code or UTF-8 text files with line-level additions, deletions, and changed blocks.</p></article>
        <article class="feature-detail"><span class="feature-icon green">⇄</span><h2>Side-by-side or unified</h2><p>Use two synchronized panes for careful review, or switch to unified output for a compact patch-style view.</p></article>
        <article class="feature-detail"><span class="feature-icon orange">⚙</span><h2>Review controls</h2><p>Select a language, ignore whitespace or case, swap versions, clear the editors, and download a unified diff.</p></article>
        <article class="feature-detail" id="privacy"><span class="feature-icon blue">◈</span><h2>Private by default</h2><p>Unsaved source is not persisted to your account. Saved comparisons are private and only visible to their owner and authorized administrators.</p></article>
        <article class="feature-detail"><span class="feature-icon purple">▣</span><h2>Saved history</h2><p>Keep selected comparisons in your account so you can return to them later. Delete them whenever you want.</p></article>
        <article class="feature-detail"><span class="feature-icon green">♛</span><h2>Lifetime Pro access</h2><p>Verified users can request access. Administrators review each request and can grant or revoke access with a recorded reason.</p></article>
    </div>
    <div class="roadmap-callout"><div><span class="eyebrow">On the roadmap</span><h2>More file types, one step at a time.</h2><p>PDF and Word extraction, spreadsheets, image comparison, folder diffs, sharing, and subscriptions are planned for future releases.</p></div><a class="button button-outline" href="{{ route('compare') }}">Try code comparison</a></div>
</div>
@endsection
