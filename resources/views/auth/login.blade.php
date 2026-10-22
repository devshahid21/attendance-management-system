@extends('layouts.app')
@section('title','Sign in')
@section('content')
<div class="auth-shell">
<section class="auth-story" aria-label="Company workspace">
<div><div class="eyebrow">A BETTER WAY TO WORK</div><h1>A little clarity.<br>A more connected <em>workday.</em></h1><p>One place for your people, daily attendance and time away. Simple, organized and always in view.</p>
<div class="auth-features"><div class="auth-feature"><span><x-icon name="clock" size="16"/></span>Effortless daily check-in and check-out</div><div class="auth-feature"><span><x-icon name="calendar" size="16"/></span>Leave requests without the paperwork</div><div class="auth-feature"><span><x-icon name="chart" size="16"/></span>A clear view of your team's attendance</div></div>
</div><div class="auth-story-foot">Built around your team. Made for everyday work.</div>
</section>
<section class="auth-form" aria-label="Sign in">
<div class="eyebrow">WELCOME TO YOUR WORKSPACE</div><h2>Good to see you.</h2><p class="welcome-copy">Sign in with your company account to continue.</p>
@if($errors->any())<div class="alert alert-danger" role="alert"><strong>Unable to sign in</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="post" action="{{ route('login.submit') }}">@csrf
<div class="mb-3"><label class="form-label" for="email">Work email</label><input id="email" type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="you@company.com" autocomplete="username" required autofocus></div>
<div class="mb-2"><label class="form-label" for="password">Password</label><input id="password" type="password" name="password" class="form-control" placeholder="Enter your password" autocomplete="current-password" required></div>
<button class="btn btn-primary w-100">Sign in to workspace<x-icon name="arrow" size="17"/></button>
</form><div class="auth-divider"></div><p class="auth-hint mb-0"><strong>Your workspace follows your role.</strong><br>Administrators manage the team. Employees access their own attendance and leave. Need an account? Contact your administrator.</p>
</section></div>
@endsection
