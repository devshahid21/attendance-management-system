@extends('layouts.app')
@section('title','My attendance')
@section('content')
<h1 class="h3">My attendance — {{ $employee->name }}</h1>
<form class="d-flex gap-2 mb-3" method="get"><input aria-label="Month" class="form-control w-auto" type="month" name="month" value="{{ $month }}"><button class="btn btn-primary">View</button></form>
@php($totals=array_count_values(array_column($days,'label')))
<div class="alert alert-light border">Present: {{ $totals['Present']??0 }} · Late: {{ $totals['Late']??0 }} · Absent: {{ $totals['Absent']??0 }}</div>
<div class="table-responsive"><table class="table table-striped"><thead><tr><th>Date</th><th>Check-in</th><th>Check-out</th><th>Status</th></tr></thead><tbody>
@foreach($days as $d)<tr><td>{{ $d['date'] }}</td><td>{{ $d['record']?->check_in_at??'—' }}</td><td>{{ $d['record']?->check_out_at??'—' }}</td>
<td><span class="badge text-bg-{{ $d['label']==='Late'?'warning':($d['label']==='Present'?'success':($d['label']==='Absent'?'danger':'secondary')) }}">{{ $d['label'] }}</span></td></tr>@endforeach
</tbody></table></div>
@endsection
