<!doctype html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>@yield('title','Attendance') · Company Attendance Management</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4"><div class="container-fluid">
<a class="navbar-brand" href="{{ route('dashboard') }}">Company Attendance</a>
@if(auth()->check())
<div class="d-flex flex-wrap gap-2 align-items-center">
<a class="btn btn-sm btn-outline-light" href="{{ route('dashboard') }}">Dashboard</a>
@if(auth()->user()->role==='admin')
<a class="btn btn-sm btn-outline-light" href="{{ route('departments.index') }}">Departments</a>
<a class="btn btn-sm btn-outline-light" href="{{ route('employees.index') }}">Employees</a>
<a class="btn btn-sm btn-outline-light" href="{{ route('attendance.index') }}">Attendance</a>
<a class="btn btn-sm btn-outline-light" href="{{ route('reports.index') }}">Reports</a>
@else
<a class="btn btn-sm btn-outline-light" href="{{ route('profile') }}">Profile</a>
<a class="btn btn-sm btn-outline-light" href="{{ route('attendance.mine') }}">My history</a>
@endif
<a class="btn btn-sm btn-outline-light" href="{{ route('leaves.index') }}">{{ auth()->user()->role==='admin'?'Leave requests':'My leave' }}</a>
<span class="text-white small">{{ auth()->user()->name }} ({{ auth()->user()->role }})</span>
<form action="{{ route('logout') }}" method="post">@csrf<button class="btn btn-sm btn-light">Log out</button></form>
</div>@endif</div></nav>
<main class="container pb-5">
@if(session('message'))<div class="alert alert-success">{{ session('message') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><strong>Please fix:</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@yield('content')
</main></body></html>
