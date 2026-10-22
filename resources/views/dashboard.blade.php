@extends('layouts.app')
@section('title','Dashboard')
@section('content')
<h1 class="h3">Dashboard</h1><p class="text-muted">Company Attendance Management</p>
@if(auth()->user()->role==='admin')
<div class="row g-3">
<div class="col-md-4"><div class="card p-4"><div class="text-muted">Active employees</div><div class="display-6">{{ $employees }}</div></div></div>
<div class="col-md-4"><div class="card p-4"><div class="text-muted">Attendance entries today</div><div class="display-6">{{ $today }}</div></div></div>
<div class="col-md-4"><a class="card p-4 text-decoration-none text-dark" href="{{ route('leaves.index',['status'=>'pending']) }}"><div class="text-muted">Pending leave requests</div><div class="display-6">{{ $pendingLeaves }}</div></a></div>
</div><p class="mt-3">Use navigation to manage employees, departments, attendance corrections, leave requests and monthly reports.</p>
@else
<div class="card shadow-sm"><div class="card-body">
<h2 class="h5">Welcome, {{ $employee->name }}</h2>
<p>Today: <span class="badge text-bg-secondary">{{ $status }}</span></p>
@if(!$employee->active)<div class="alert alert-warning">Your employee profile is inactive.</div>
@elseif(!$attendance)
<form method="post" action="{{ route('checkin') }}">@csrf<button class="btn btn-success">Check in</button></form>
@elseif(!$attendance->check_out_at)
<p>Checked in at {{ $attendance->check_in_at }}</p>
<form method="post" action="{{ route('checkout') }}">@csrf<button class="btn btn-primary">Check out</button></form>
@else<p>Checked in: {{ $attendance->check_in_at }} · Checked out: {{ $attendance->check_out_at }}</p>@endif
</div></div>
@endif
@endsection
