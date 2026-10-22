@extends('layouts.app')
@section('title','Departments')
@section('content')
<h1 class="h3">Departments</h1>
<div class="card p-3 mb-3"><form action="{{ route('departments.store') }}" method="post" class="row g-2">@csrf
<div class="col-md-8"><label for="name" class="form-label">New department</label><input id="name" name="name" class="form-control" required maxlength="100"></div>
<div class="col-md-4 d-flex align-items-end"><button class="btn btn-primary">Add department</button></div></form></div>
<div class="table-responsive"><table class="table table-striped align-middle"><thead><tr><th>Department</th><th>Employees</th><th>Rename</th><th>Remove</th></tr></thead><tbody>
@foreach($items as $item)<tr><td>{{ $item->name }}</td><td>{{ $item->employees_count }}</td>
<td><form class="d-flex gap-2" action="{{ route('departments.update',$item) }}" method="post">@csrf @method('PUT')<input class="form-control" name="name" value="{{ $item->name }}" required><button class="btn btn-sm btn-outline-primary">Save</button></form></td>
<td><form method="post" action="{{ route('departments.destroy',$item) }}" onsubmit="return confirm('Delete this empty department?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" @disabled($item->employees_count>0)>Delete</button></form></td></tr>
@empty<tr><td colspan="4">No departments yet.</td></tr>@endforelse
</tbody></table></div>{{ $items->links('pagination::bootstrap-5') }}
@endsection
