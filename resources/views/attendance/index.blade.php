@extends('layouts.app')
@section('title','Attendance')
@section('content')
<div class="d-flex justify-content-between align-items-center"><h1 class="h3">Attendance by date</h1><a class="btn btn-primary" href="{{ route('attendance.manual') }}">Add missed-day correction</a></div>
<form class="row g-2 mb-3" method="get">
<div class="col-md-3"><input aria-label="Date" class="form-control" type="date" name="date" value="{{ $date }}"></div>
<div class="col-md-3"><select aria-label="Department" class="form-select" name="department_id"><option value="">All departments</option>@foreach($departments as $d)<option value="{{ $d->id }}" @selected(request('department_id')==$d->id)>{{ $d->name }}</option>@endforeach</select></div>
<div class="col-md-4"><select aria-label="Employee" class="form-select" name="employee_id"><option value="">All employees</option>@foreach($employees as $e)<option value="{{ $e->id }}" @selected(request('employee_id')==$e->id)>{{ $e->name }}</option>@endforeach</select></div>
<div class="col-md-2"><button class="btn btn-primary">Filter</button></div></form>
<div class="table-responsive"><table class="table table-striped"><thead><tr><th>Employee</th><th>Department</th><th>Check-in</th><th>Check-out</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($items as $e)
@php($record=$e->attendances->first())
@php($label=\App\Support\AttendanceRules::label(\Carbon\Carbon::parse($date,'Asia/Karachi'),$record,$e->active,$e->leaveRequests->isNotEmpty()))
<tr><td>{{ $e->name }}</td><td>{{ $e->department->name }}</td><td>{{ $record?->check_in_at??'—' }}</td><td>{{ $record?->check_out_at??'—' }}</td>
<td><span class="badge text-bg-{{ $label==='Late'?'warning':($label==='Present'?'success':($label==='Absent'?'danger':'secondary')) }}">{{ $label }}</span></td>
<td>@if($record)<a href="{{ route('attendance.edit',$record) }}" class="btn btn-sm btn-outline-primary">Correct</a>@endif</td></tr>
@empty<tr><td colspan="6">No matching employees.</td></tr>@endforelse</tbody></table></div>
{{ $items->links('pagination::bootstrap-5') }}
@endsection
