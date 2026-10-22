@extends('layouts.app')
@section('title','Monthly reports')
@section('content')
<h1 class="h3">Monthly reports</h1>
<form method="get" class="row g-2 mb-3">
<div class="col-md-3"><input class="form-control" aria-label="Month" name="month" type="month" value="{{ $month }}"></div>
<div class="col-md-3"><select aria-label="Department" class="form-select" name="department_id"><option value="">All departments</option>@foreach($departments as $d)<option value="{{ $d->id }}" @selected(request('department_id')==$d->id)>{{ $d->name }}</option>@endforeach</select></div>
<div class="col-md-4"><select aria-label="Employee" class="form-select" name="employee_id"><option value="">All employees</option>@foreach($employees as $e)<option value="{{ $e->id }}" @selected(request('employee_id')==$e->id)>{{ $e->name }}</option>@endforeach</select></div>
<div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div></form>
<a class="btn btn-outline-success mb-3" href="{{ route('reports.csv',request()->query()) }}">Export CSV</a>
<div class="table-responsive"><table class="table table-striped"><thead><tr><th>Code</th><th>Employee</th><th>Department</th><th>Present</th><th>Late</th><th>Absent</th></tr></thead><tbody>
@forelse($rows as $row)<tr><td>{{ $row['employee']->employee_code }}</td><td>{{ $row['employee']->name }}</td><td>{{ $row['employee']->department->name }}</td><td>{{ $row['present'] }}</td><td>{{ $row['late'] }}</td><td>{{ $row['absent'] }}</td></tr>
@empty<tr><td colspan="6">No employees.</td></tr>@endforelse</tbody></table></div>
@endsection
