@extends('layouts.app')
@section('title','Salary management')
@section('content')
<div class="page-heading"><div><div class="eyebrow">SUPER ADMIN ONLY</div><h1>Salary management</h1><p>Monthly base salaries in Pakistani rupees. Private salary details are restricted to Super Admin.</p></div></div>
<div class="panel p-4 mb-4"><form method="get" class="row g-3 align-items-end">
<div class="col-md-4"><label class="form-label">Salary month</label><input type="month" name="month" class="form-control" value="{{ $month }}" required></div>
<div class="col-md-5"><label class="form-label">Search employee</label><input name="q" class="form-control" value="{{ request('q') }}" placeholder="Employee name"></div>
<div class="col-md-3"><button class="btn btn-primary w-100">Filter</button></div>
</form></div>
<div class="panel p-4"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Employee</th><th>Department</th><th>Monthly salary (PKR)</th><th>Update</th></tr></thead><tbody>
@forelse($employees as $employee)
<tr><td><strong>{{ $employee->name }}</strong><br><small>{{ $employee->employee_code }}</small></td><td>{{ $employee->department->name }}</td>
<td>{{ isset($salaries[$employee->id]) ? number_format((float)$salaries[$employee->id]->amount,2) : 'Not set' }}</td>
<td><form action="{{ route('salaries.store') }}" method="post" class="d-flex flex-wrap gap-2">
@csrf<input type="hidden" name="employee_id" value="{{ $employee->id }}"><input type="hidden" name="month" value="{{ $month }}">
<input aria-label="Salary amount for {{ $employee->name }}" class="form-control" style="max-width:160px" type="number" name="amount" min="0" max="99999999.99" step="0.01" value="{{ old('amount', $salaries[$employee->id]->amount ?? '') }}" required>
<input aria-label="Salary note for {{ $employee->name }}" class="form-control" style="max-width:200px" name="note" maxlength="255" placeholder="Optional note" value="{{ $salaries[$employee->id]->note ?? '' }}">
<button class="btn btn-primary">Save</button></form></td></tr>
@empty<tr><td colspan="4">No employees matched.</td></tr>@endforelse
</tbody></table></div>{{ $employees->links('pagination::bootstrap-5') }}</div>
<div class="alert alert-info mt-3">This module stores monthly base salary records only. It does not pay employees, calculate taxes, or generate payslips.</div>
@endsection
