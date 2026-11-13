@extends('layouts.app')
@section('title', 'Compare code, text, files and more')
@section('content')
<div class="page-shell home-page">
    <section class="hero panel">
        <div class="hero-copy">
            <span class="eyebrow"><span class="eyebrow-dot"></span> A clearer way to compare</span>
            <h1>Compare <span>Code, Text,</span><br>Files and More</h1>
            <p class="hero-lede">Find differences, merge changes, and stay in control of every line.</p>
            <p class="hero-sub">A fast, private and developer-friendly difference checker.</p>
            <div class="hero-actions"><a class="button" href="{{ route('compare') }}">Start comparing <span>→</span></a><a class="button button-outline" href="{{ route('pricing') }}">View pricing</a></div>
            <div class="trust-row"><span><i class="check">✓</i> No code execution</span><span><i class="check">✓</i> Private by default</span></div>
        </div>
        <div class="hero-art" aria-hidden="true">
            <div class="glow-orb"></div>
            <div class="floating-code code-back"><div class="window-dots"><i></i><i></i><i></i></div><div class="code-line purple-line"></div><div class="code-line short"></div><div class="code-line green-line"></div><div class="code-line medium"></div><div class="code-line red-line"></div></div>
            <div class="floating-code code-front"><div class="window-dots"><i></i><i></i><i></i></div><div class="mock-row"><b>01</b><span class="purple-line"></span></div><div class="mock-row"><b>02</b><span></span></div><div class="mock-row"><b>03</b><span class="green-line"></span></div><div class="mock-row changed"><b>04</b><span></span></div><div class="mock-row"><b>05</b><span class="medium"></span></div><div class="mock-row added"><b>06</b><span class="green-line"></span></div></div>
            <div class="code-badge">&lt; / &gt;</div>
        </div>
        <div class="hero-pills"><a href="{{ route('compare') }}"><span class="pill-icon">⌘</span> Code &amp; Text</a><span><span class="pill-icon">▤</span> PDF &amp; Word <small>Later</small></span><span><span class="pill-icon green">▦</span> Spreadsheets <small>Later</small></span><span><span class="pill-icon">▧</span> Images <small>Later</small></span><span><span class="pill-icon">▱</span> Folders <small>Later</small></span><span><span class="pill-icon">⇄</span> Merge <small>Later</small></span></div>
    </section>

    <section class="section-block home-intro" id="how-it-works"><div><span class="eyebrow">Simple and focused</span><h2>See every change at a glance.</h2><p>Paste code or text, choose how to compare it, and review a readable diff in seconds. Your source stays out of analytics and is only saved when you choose to keep a comparison.</p></div><a class="text-link" href="{{ route('features') }}">Explore features <span>→</span></a></section>
    <section class="feature-grid">
        <article class="feature-card"><span class="feature-icon purple">⌘</span><h3>Made for code</h3><p>Choose a language, compare line by line, and spot changed blocks without losing context.</p></article>
        <article class="feature-card"><span class="feature-icon green">⇄</span><h3>Two ways to review</h3><p>Keep versions side by side or switch to a compact unified view for quick reviews.</p></article>
        <article class="feature-card"><span class="feature-icon orange">♢</span><h3>Your work stays yours</h3><p>Comparisons are private by default. Save only the versions you want in your history.</p></article>
    </section>
    <section class="faq-section"><div><span class="eyebrow">Quick answers</span><h2>Frequently asked questions</h2></div><div class="faq-list"><details><summary>Are my comparisons saved?</summary><p>No. A comparison is saved to your account only when you sign in and select the save option. You can delete saved comparisons at any time.</p></details><details><summary>Does CodeDiff execute uploaded code?</summary><p>No. Uploads are read as UTF-8 text for comparison only. CodeDiff does not execute the content.</p></details><details><summary>How do I get Lifetime Pro?</summary><p>Verify your account, submit a short request, and an administrator will review it. An approved grant stays active until an administrator revokes it.</p></details></div></section>
    <section class="bottom-cta"><div><span class="eyebrow">Ready when you are</span><h2>Make your next review easier.</h2></div><a class="button" href="{{ route('compare') }}">Open the diff tool <span>→</span></a></section>
</div>
@endsection
