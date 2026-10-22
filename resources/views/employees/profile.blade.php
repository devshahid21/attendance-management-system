@extends('layouts.app')
@section('title','My profile')
@section('content')
<h1 class="h3">My profile</h1><div class="card p-4">
<p><strong>Name:</strong> {{ $employee->name }}</p><p><strong>Code:</strong> {{ $employee->employee_code }}</p>
<p><strong>Email:</strong> {{ $employee->email }}</p><p><strong>Department:</strong> {{ $employee->department->name }}</p>
<p><strong>Status:</strong> {{ $employee->active?'Active':'Inactive' }}</p></div>
@endsection
