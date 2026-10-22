@extends('layouts.app')
@section('title',$employee->exists?'Edit employee':'Add employee')
@section('content')
<h1 class="h3">{{ $employee->exists?'Edit employee':'Add employee' }}</h1>
<div class="card p-4"><form method="post" action="{{ $employee->exists?route('employees.update',$employee):route('employees.store') }}">
@csrf @if($employee->exists)@method('PUT')@endif
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Full name</label><input class="form-control" required name="name" value="{{ old('name',$employee->name) }}"></div>
<div class="col-md-6"><label class="form-label">Employee code</label><input class="form-control" required name="employee_code" value="{{ old('employee_code',$employee->employee_code) }}"></div>
<div class="col-md-6"><label class="form-label">Email / login</label><input type="email" class="form-control" required name="email" value="{{ old('email',$employee->email) }}"></div>
<div class="col-md-6"><label class="form-label">Department</label><select class="form-select" name="department_id" required>
<option value="">Select</option>@foreach($departments as $d)<option value="{{ $d->id }}" @selected(old('department_id',$employee->department_id)==$d->id)>{{ $d->name }}</option>@endforeach</select></div>
<div class="col-md-6"><label class="form-label">Password {{ $employee->exists?'(blank = unchanged)':'' }}</label><input type="password" class="form-control" name="password" {{ $employee->exists?'':'required' }} minlength="8"></div>
<div class="col-md-6"><label class="form-label">Confirm password</label><input type="password" class="form-control" name="password_confirmation" {{ $employee->exists?'':'required' }}></div>
</div><div class="mt-4"><button class="btn btn-primary">Save employee</button> <a class="btn btn-link" href="{{ route('employees.index') }}">Cancel</a></div>
</form></div>
@endsection
