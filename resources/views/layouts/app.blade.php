<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
<meta name="theme-color" content="#142d28">
<title>@yield('title','Overview') · Company Attendance</title>
<link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('css/app.css') }}" rel="stylesheet">
<script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body class="{{ auth()->check()?'workspace':'auth-page' }}">
<a class="skip-link" href="#main-content">Skip to content</a>
@if(auth()->check())
@php
$isAdmin=auth()->user()->role==='admin';
$nav=$isAdmin?[
['dashboard','Overview','grid'],['employees.*','Team directory','users'],['departments.*','Departments','building'],
['attendance.index','Attendance','clock'],['leaves.*','Leave requests','calendar'],['reports.*','Reports','chart'],['access.index','Roles & access','shield']
]:[
['dashboard','Overview','grid'],['attendance.mine','My attendance','clock'],['leaves.*','My leave','calendar'],['profile','My profile','user']
];
$targets=['employees.*'=>'employees.index','departments.*'=>'departments.index','leaves.*'=>'leaves.index','reports.*'=>'reports.index'];
@endphp
<div class="nav-scrim" data-nav-close></div>
<aside class="sidebar" id="workspace-navigation" aria-label="Workspace navigation">
<a class="brand" href="{{ route('dashboard') }}"><span class="brand-mark"><x-icon name="leaf" size="24"/></span><span>Attendance<span class="brand-sub">COMPANY WORKSPACE</span></span></a>
<div class="workspace-label">{{ $isAdmin?'ADMIN WORKSPACE':'EMPLOYEE WORKSPACE' }}</div>
<nav class="side-nav" aria-label="Main">
@foreach($nav as [$key,$label,$icon])
<a href="{{ route($targets[$key]??$key) }}" class="nav-item {{ request()->routeIs($key)?'active':'' }}" @if(request()->routeIs($key)) aria-current="page" @endif><x-icon :name="$icon"/><span>{{ $label }}</span>@if(request()->routeIs($key))<span class="nav-dot"></span>@endif</a>
@endforeach
</nav>
<div class="sidebar-bottom">
<a href="{{ route('account.edit') }}" class="nav-item {{ request()->routeIs('account.*')?'active':'' }}"><x-icon name="lock"/><span>Account settings</span></a>
<div class="sidebar-help"><x-icon name="shield" size="18"/><div><strong>{{ $isAdmin?'Manage with confidence':'Your work, in one place' }}</strong><p>{{ $isAdmin?'People, attendance and approvals.':'Attendance, leave and your profile.' }}</p></div></div>
<div class="sidebar-person"><span class="avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name,0,1)) }}</span><div><strong>{{ auth()->user()->name }}</strong><span>{{ $isAdmin?'Administrator':'Employee' }}</span></div></div>
</div>
</aside>
<div class="workspace-main">
<header class="topbar">
<div class="topbar-left"><button type="button" class="icon-button mobile-nav-toggle" data-nav-toggle aria-controls="workspace-navigation" aria-expanded="false" aria-label="Open navigation"><x-icon name="menu"/></button><span class="breadcrumb-root">{{ $isAdmin?'Admin portal':'Employee portal' }}</span><x-icon name="chevron" size="14"/><span class="breadcrumb-page">@yield('title','Overview')</span></div>
<div class="topbar-right"><span class="today-label"><x-icon name="calendar" size="16"/>{{ now('Asia/Karachi')->format('D, d M Y') }}</span><form action="{{ route('logout') }}" method="post">@csrf<button class="btn btn-sm btn-signout"><x-icon name="logout" size="17"/><span>Sign out</span></button></form></div>
</header>
<main id="main-content" class="page-content" tabindex="-1">
@if(session('message'))<div class="alert alert-success app-alert" role="status"><x-icon name="check"/><span>{{ session('message') }}</span><button type="button" aria-label="Dismiss message" class="alert-dismiss" data-dismiss-alert>×</button></div>@endif
@if($errors->any())<div class="alert alert-danger app-alert" role="alert"><div><strong>Please check the following</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>@endif
@yield('content')
<footer class="page-footer"><span>Company Attendance Management</span><span>All times in Pakistan Standard Time</span></footer>
</main>
</div>
@else
<header class="auth-header"><a class="brand brand-dark" href="{{ route('login') }}"><span class="brand-mark"><x-icon name="leaf" size="24"/></span><span>Attendance<span class="brand-sub">COMPANY WORKSPACE</span></span></a><span class="auth-tag"><x-icon name="shield" size="16"/>Your company workspace</span></header>
<main id="main-content" tabindex="-1">
@yield('content')
</main>
@endif
</body></html>
