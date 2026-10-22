@extends('layouts.app')
@section('title','Correct attendance')
@section('content')
<h1 class="h3">Correct attendance: {{ $attendance->employee->name }}</h1>
<p>Date: {{ $attendance->attendance_date->format('Y-m-d') }}</p>
<form method="post" action="{{ route('attendance.update',$attendance) }}" class="card p-4 mb-4">@csrf @method('PUT')
<div class="row g-3"><div class="col-md-6"><label class="form-label" for="correction-checkin">Check-in</label><input class="form-control" type="time" id="correction-checkin" name="check_in_at" required value="{{ old('check_in_at',substr($attendance->check_in_at??'',0,5)) }}"></div>
<div class="col-md-6"><label class="form-label" for="correction-checkout">Check-out</label><input class="form-control" type="time" id="correction-checkout" name="check_out_at" value="{{ old('check_out_at',substr($attendance->check_out_at??'',0,5)) }}"></div>
<div class="col-12"><label class="form-label" for="correction-reason">Reason (required)</label><textarea id="correction-reason" name="reason" class="form-control" rows="3" required minlength="5">{{ old('reason') }}</textarea></div>
</div><div class="mt-3"><button class="btn btn-primary">Save correction</button></div></form>
<h2 class="h5">Correction history</h2>
<div class="table-responsive"><table class="table table-striped"><thead><tr><th>When</th><th>Admin</th><th>Reason</th><th>Before</th><th>After</th></tr></thead><tbody>
@forelse($attendance->corrections as $c)<tr><td>{{ $c->created_at }}</td><td>{{ $c->admin->name }}</td><td>{{ $c->reason }}</td>
<td><code>{{ json_encode($c->before) }}</code></td><td><code>{{ json_encode($c->after) }}</code></td></tr>
@empty<tr><td colspan="5">No corrections.</td></tr>@endforelse</tbody></table></div>
@endsection
