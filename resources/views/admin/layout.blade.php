@extends('layouts.app')
@section('content')
<div class="page-shell app-layout admin-layout">
    <aside class="app-sidebar admin-sidebar"><div class="sidebar-label">ADMINISTRATION</div><a class="side-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><span>▦</span> Dashboard</a><a class="side-link {{ request()->routeIs('admin.users') ? 'active' : '' }}" href="{{ route('admin.users') }}"><span>♙</span> Users</a><a class="side-link {{ request()->routeIs('admin.requests*') ? 'active' : '' }}" href="{{ route('admin.requests') }}"><span>♛</span> Lifetime Requests</a><a class="side-link {{ request()->routeIs('admin.grants') ? 'active' : '' }}" href="{{ route('admin.grants') }}"><span>◈</span> Access Grants</a><a class="side-link {{ request()->routeIs('admin.audit') ? 'active' : '' }}" href="{{ route('admin.audit') }}"><span>☷</span> Audit Log</a></aside>
    <div class="app-content admin-content">@yield('admin-content')</div>
</div>
@endsection
