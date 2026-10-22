@extends('layouts.app')
@section('title','Employees')
@section('content')
<div class="d-flex justify-content-between align-items-center"><h1 class="h3">Employees</h1><a class="btn btn-primary" href="{{ route('employees.create') }}">Add employee</a></div>
<form class="row g-2 my-3" method="get">
<div class="col-md-5"><input class="form-control" aria-label="Search name or employee code" name="q" placeholder="Search name or code" value="{{ request('q') }}"></div>
<div class="col-md-5"><select class="form-select" aria-label="Department" name="department_id"><option value="">All departments</option>@foreach($departments as $d)<option value="{{ $d->id }}" @selected(request('department_id')==$d->id)>{{ $d->name }}</option>@endforeach</select></div>
<div class="col-md-2"><button class="btn btn-outline-primary w-100">Filter</button></div></form>
<div class="table-responsive"><table class="table table-striped"><thead><tr><th>Code</th><th>Name</th><th>Department</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($items as $e)<tr><td>{{ $e->employee_code }}</td><td>{{ $e->name }}</td><td>{{ $e->department->name }}</td>
<td><span class="badge text-bg-{{ $e->active?'success':'secondary' }}">{{ $e->active?'Active':'Inactive' }}</span></td>
<td><a href="{{ route('employees.show',$e) }}" class="btn btn-sm btn-outline-primary">View</a> <a href="{{ route('employees.edit',$e) }}" class="btn btn-sm btn-outline-secondary">Edit</a></td></tr>
@empty<tr><td colspan="5">No employees found.</td></tr>@endforelse
</tbody></table></div>{{ $items->links('pagination::bootstrap-5') }}
@endsection
