@extends('layouts.app')
@section('title',$employee->exists?'Edit employee':'Add employee')
@section('content')
<h1 class="h3">{{ $employee->exists?'Edit employee':'Add employee' }}</h1>
<div class="card p-4"><form method="post" action="{{ $employee->exists?route('employees.update',$employee):route('employees.store') }}">
@csrf @if($employee->exists)@method('PUT')@endif
<div class="row g-3">
<div class="col-md-6"><label class="form-label" for="employee-name">Full name</label><input class="form-control" required id="employee-name" name="name" value="{{ old('name',$employee->name) }}"></div>
<div class="col-md-6"><label class="form-label" for="employee-employee_code">Employee code</label><input class="form-control" required id="employee-employee_code" name="employee_code" value="{{ old('employee_code',$employee->employee_code) }}"></div>
<div class="col-md-6"><label class="form-label" for="employee-email">Email / login</label><input type="email" class="form-control" required id="employee-email" name="email" value="{{ old('email',$employee->email) }}"></div>
<div class="col-md-6"><label class="form-label" for="employee-department_id">Department</label><select class="form-select" id="employee-department_id" name="department_id" required>
<option value="">Select</option>@foreach($departments as $d)<option value="{{ $d->id }}" @selected(old('department_id',$employee->department_id)==$d->id)>{{ $d->name }}</option>@endforeach</select></div>
<div class="col-md-6"><label class="form-label" for="employee-password">Password {{ $employee->exists?'(blank = unchanged)':'' }}</label><input type="password" class="form-control" id="employee-password" name="password" autocomplete="new-password" {{ $employee->exists?'':'required' }} minlength="8"></div>
<div class="col-md-6"><label class="form-label" for="employee-password-confirm">Confirm password</label><input type="password" class="form-control" id="employee-password-confirm" name="password_confirmation" autocomplete="new-password" {{ $employee->exists?'':'required' }}></div>
</div><div class="mt-4"><button class="btn btn-primary">Save employee</button> <a class="btn btn-link" href="{{ route('employees.index') }}">Cancel</a></div>
</form></div>
@endsection
