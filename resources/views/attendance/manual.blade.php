@extends('layouts.app')
@section('title','Record missed attendance')
@section('content')
<h1 class="h3">Correct a missing attendance day</h1>
<p class="text-muted">Admin-only action. A reason and before/after audit record are saved.</p>
<form class="card p-4" method="post" action="{{ route('attendance.manual.store') }}">@csrf
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Employee</label><select name="employee_id" class="form-select" required>
<option value="">Choose employee</option>@foreach($employees as $e)<option value="{{ $e->id }}" @selected(old('employee_id')==$e->id)>{{ $e->name }} ({{ $e->employee_code }})</option>@endforeach</select></div>
<div class="col-md-6"><label class="form-label">Working date</label><input class="form-control" name="attendance_date" type="date" required max="{{ now('Asia/Karachi')->format('Y-m-d') }}" value="{{ old('attendance_date') }}"></div>
<div class="col-md-6"><label class="form-label">Check in</label><input class="form-control" name="check_in_at" type="time" value="{{ old('check_in_at') }}" required></div>
<div class="col-md-6"><label class="form-label">Check out (optional)</label><input class="form-control" name="check_out_at" type="time" value="{{ old('check_out_at') }}"></div>
<div class="col-12"><label class="form-label">Reason for adding record</label><textarea class="form-control" name="reason" required minlength="5" rows="3">{{ old('reason') }}</textarea></div>
</div><div class="mt-3"><button class="btn btn-primary">Save correction</button> <a href="{{ route('attendance.index') }}" class="btn btn-link">Cancel</a></div>
</form>
@endsection
