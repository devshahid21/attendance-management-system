@extends('layouts.app')
@section('title','My profile')
@section('content')
<div class="page-heading"><div><div class="eyebrow">YOUR DETAILS</div><h1>My profile</h1><p>Your employee information, maintained by your administrator.</p></div><a class="btn btn-outline-primary" href="{{ route('account.edit') }}"><x-icon name="lock" size="16"/>Account settings</a></div>
<section class="panel">
<div class="profile-banner"><span class="avatar">{{ mb_strtoupper(mb_substr($employee->name,0,1)) }}</span><div><h2>{{ $employee->name }}</h2><p>{{ $employee->department->name }} · {{ $employee->employee_code }}</p></div></div>
<div class="panel-body"><dl class="row mb-0"><dt class="col-sm-3 mb-3">Employee ID</dt><dd class="col-sm-9 mb-3">{{ $employee->employee_code }}</dd><dt class="col-sm-3 mb-3">Work email</dt><dd class="col-sm-9 mb-3" style="overflow-wrap:anywhere">{{ $employee->email }}</dd><dt class="col-sm-3 mb-3">Department</dt><dd class="col-sm-9 mb-3">{{ $employee->department->name }}</dd><dt class="col-sm-3 mb-3">Account status</dt><dd class="col-sm-9 mb-3"><span class="status-pill {{ $employee->active?'status-present':'status-pending' }}">{{ $employee->active?'Active':'Inactive' }}</span></dd></dl></div>
</section>
@endsection
