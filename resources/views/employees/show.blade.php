@extends('layouts.app')
@section('title','Employee')
@section('content')
<h1 class="h3">{{ $employee->name }}</h1><div class="card p-4">
<dl class="row"><dt class="col-sm-3">Code</dt><dd class="col-sm-9">{{ $employee->employee_code }}</dd>
<dt class="col-sm-3">Email</dt><dd class="col-sm-9">{{ $employee->email }}</dd>
<dt class="col-sm-3">Department</dt><dd class="col-sm-9">{{ $employee->department->name }}</dd>
<dt class="col-sm-3">Status</dt><dd class="col-sm-9">{{ $employee->active?'Active':'Inactive' }}</dd></dl>
<div class="d-flex gap-2"><a class="btn btn-primary" href="{{ route('employees.edit',$employee) }}">Edit</a>
@if($employee->active)<form action="{{ route('employees.deactivate',$employee) }}" method="post" data-confirm="Deactivate this employee? Attendance history will remain.">@csrf<button class="btn btn-outline-danger">Deactivate</button></form>
@else<form action="{{ route('employees.activate',$employee) }}" method="post">@csrf<button class="btn btn-outline-success">Reactivate</button></form>@endif
</div></div>
@endsection
