@extends('layouts.app')
@section('title', $admin ? 'Leave requests' : 'My leave')
@section('content')
<h1 class="h3">{{ $admin ? 'Leave requests' : 'My leave requests' }}</h1>
@if(!$admin && auth()->user()->employee->active)
<div class="card shadow-sm mb-4"><div class="card-body">
<h2 class="h5">Request leave</h2>
<p class="text-muted small">Choose dates from today onward, up to 31 calendar days. Only working days count as leave in attendance reports. No paid-leave balance is calculated.</p>
<form method="post" action="{{ route('leaves.store') }}" class="row g-3">@csrf
<div class="col-md-4"><label class="form-label" for="leave-type">Type</label><select id="leave-type" name="type" class="form-select">
@foreach(['casual','sick','annual'] as $type)<option value="{{ $type }}" @selected(old('type')===$type)>{{ ucfirst($type) }}</option>@endforeach
</select></div>
<div class="col-md-4"><label class="form-label" for="leave-start">From</label><input id="leave-start" class="form-control" type="date" name="start_date" value="{{ old('start_date') }}" min="{{ now('Asia/Karachi')->toDateString() }}" required></div>
<div class="col-md-4"><label class="form-label" for="leave-end">To</label><input id="leave-end" class="form-control" type="date" name="end_date" value="{{ old('end_date') }}" min="{{ now('Asia/Karachi')->toDateString() }}" required></div>
<div class="col-12"><label class="form-label" for="leave-reason">Reason</label><textarea id="leave-reason" class="form-control" name="reason" rows="2" minlength="5" maxlength="1000" required>{{ old('reason') }}</textarea></div>
<div class="col-12"><button class="btn btn-primary">Submit request</button></div>
</form></div></div>
@elseif(!$admin)<div class="alert alert-warning">Your profile is inactive. You can view history but cannot request new leave.</div>@endif
<form method="get" class="d-flex gap-2 mb-3">
<label class="visually-hidden" for="status-filter">Status</label>
<select id="status-filter" name="status" class="form-select w-auto"><option value="">All statuses</option>
@foreach(['pending','approved','rejected'] as $state)<option value="{{ $state }}" @selected(request('status')===$state)>{{ ucfirst($state) }}</option>@endforeach
</select><button class="btn btn-outline-primary">Filter</button></form>
<div class="table-responsive"><table class="table table-striped align-middle">
<thead><tr>@if($admin)<th>Employee</th>@endif<th>Dates</th><th>Type / reason</th><th>Status</th><th>Review</th></tr></thead>
<tbody>@forelse($items as $item)<tr>
@if($admin)<td>{{ $item->employee->name }}<br><small>{{ $item->employee->department->name }}</small></td>@endif
<td class="text-nowrap">{{ $item->start_date->format('d M Y') }}<br>to {{ $item->end_date->format('d M Y') }}</td>
<td><strong>{{ ucfirst($item->type) }}</strong><div style="white-space:pre-wrap;overflow-wrap:anywhere">{{ $item->reason }}</div></td>
<td><span class="badge text-bg-{{ $item->status==='approved'?'success':($item->status==='rejected'?'danger':'warning') }}">{{ ucfirst($item->status) }}</span></td>
<td>@if($admin && $item->status==='pending')
<form method="post" action="{{ route('leaves.review',$item) }}">@csrf @method('PATCH')
<label class="visually-hidden" for="decision-{{ $item->id }}">Decision</label>
<select id="decision-{{ $item->id }}" name="status" class="form-select form-select-sm mb-2"><option value="approved">Approve</option><option value="rejected">Reject</option></select>
<label class="visually-hidden" for="note-{{ $item->id }}">Review note</label>
<textarea id="note-{{ $item->id }}" name="review_note" class="form-control form-control-sm mb-2" rows="2" maxlength="1000" placeholder="Reason required when rejecting"></textarea>
<button class="btn btn-sm btn-primary">Save decision</button></form>
@else
{{ $item->reviewer?->name ?? 'Awaiting admin review' }}
@if($item->reviewed_at)<br><small>{{ $item->reviewed_at->format('d M Y H:i') }}</small>@endif
@if($item->review_note)<div style="white-space:pre-wrap;overflow-wrap:anywhere">{{ $item->review_note }}</div>@endif
@endif</td></tr>
@empty<tr><td colspan="{{ $admin?5:4 }}" class="text-muted">No leave requests found.</td></tr>@endforelse</tbody>
</table></div>
{{ $items->links('pagination::bootstrap-5') }}
@endsection
