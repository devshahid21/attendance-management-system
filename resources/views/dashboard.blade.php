@extends('layouts.app')
@section('title','Overview')
@section('content')
@if(in_array(auth()->user()->role,['admin','superadmin'],true))
<div class="page-heading"><div><div class="eyebrow">WORKSPACE OVERVIEW</div><h1>A clear view of your team.</h1><p>Welcome back, {{ \Illuminate\Support\Str::before(auth()->user()->name,' ') }}. Here is how your workplace is doing.</p></div><a class="btn btn-primary" href="{{ route('employees.create') }}"><x-icon name="plus" size="16"/>Add employee</a></div>
<div class="metric-grid">
@foreach([['Active employees',$employees,'People in your workspace','users',''],['Checked in today',$today,'Attendance recorded today','check','blue'],['On leave today',$onLeave,'Approved leave, no check-in','calendar','purple'],['Pending requests',$pendingLeaves,'Leave requests awaiting review','clock','amber']] as [$label,$value,$caption,$icon,$color])
<div class="metric"><div class="metric-top"><span>{{ $label }}</span><span class="metric-icon {{ $color }}"><x-icon :name="$icon" size="17"/></span></div><div class="metric-value">{{ str_pad((string)$value,2,'0',STR_PAD_LEFT) }}</div><div class="metric-caption">{{ $caption }}</div></div>
@endforeach
</div>
<div class="dashboard-split">
<section class="panel"><div class="panel-header"><div><h2>Attendance activity</h2><p>Recorded check-ins over the last 7 days</p></div><span class="status-pill status-present">Last 7 days</span></div>
<div class="weekly-chart" role="img" aria-label="Daily recorded check-ins: {{ collect($week)->map(fn($d)=>$d['label'].' '.$d['date'].': '.$d['count'])->implode('; ') }}">
@foreach($week as $d)<div class="chart-column"><span class="chart-count">{{ $d['count'] }}</span><div class="chart-bar" style="height:{{ max(2,round($d['count']/$chartMax*125)) }}px"></div><span class="chart-day">{{ $d['label'] }}</span></div>@endforeach
</div><div class="chart-note"><span><span class="legend-dot"></span>Employee check-ins</span><span>Pakistan Standard Time</span></div></section>
<section class="panel"><div class="panel-header"><div><h2>Keep things moving</h2><p>Your everyday workspace shortcuts</p></div></div><div class="action-list">
@foreach([['employees.index','Team directory','Manage people and employee accounts','users'],['leaves.index','Review leave requests',$pendingLeaves.' request'.($pendingLeaves===1?'':'s').' waiting for a decision','calendar'],['reports.index','Monthly reports','Review attendance and export a CSV','chart']] as [$target,$label,$caption,$icon])
<a class="action-row" href="{{ route($target) }}"><span class="action-icon"><x-icon :name="$icon" size="18"/></span><span><strong>{{ $label }}</strong><small>{{ $caption }}</small></span><x-icon name="chevron" size="15"/></a>
@endforeach</div></section>
</div>
<section class="panel"><div class="panel-header"><div><h2>Recent attendance</h2><p>The latest recorded activity across your team</p></div><a class="text-link" href="{{ route('attendance.index') }}">View attendance<x-icon name="arrow" size="15"/></a></div>
<div class="table-responsive"><table class="table"><thead><tr><th>Team member</th><th>Department</th><th>Date</th><th>Check-in</th><th>Status</th></tr></thead><tbody>
@forelse($recent as $entry)<tr><td><div class="person-cell"><span class="avatar">{{ mb_strtoupper(mb_substr($entry->employee->name,0,1)) }}</span><span><strong>{{ $entry->employee->name }}</strong><small>{{ $entry->employee->employee_code }}</small></span></div></td><td>{{ $entry->employee->department->name }}</td><td>{{ $entry->attendance_date->format('d M Y') }}</td><td>{{ $entry->check_in_at??'—' }}</td><td><span class="status-pill status-{{ $entry->status }}">{{ ucfirst($entry->status) }}</span></td></tr>
@empty<tr><td colspan="5"><div class="empty-state"><x-icon name="clock" size="28"/><strong>Your team's activity will appear here.</strong><p>Once employees check in, you will see their latest attendance.</p></div></td></tr>@endforelse
</tbody></table></div></section>
@else
<div class="page-heading"><div><div class="eyebrow">YOUR WORKDAY</div><h1>Your day, at a glance.</h1><p>Hello, {{ \Illuminate\Support\Str::before($employee->name,' ') }}. Your attendance and time away, all in one place.</p></div><a class="btn btn-outline-primary" href="{{ route('leaves.index') }}"><x-icon name="plus" size="16"/>Request leave</a></div>
<div class="metric-grid">
@foreach([['Present days',$summary['Present'],'Recorded this calendar month','check',''],['Late arrivals',$summary['Late'],'Recorded this calendar month','clock','amber'],['Days on leave',$summary['Leave'],'Approved working days so far','calendar','purple'],['Pending requests',$pendingLeaves,'Your leave awaiting review','mail','blue']] as [$label,$value,$caption,$icon,$color])
<div class="metric"><div class="metric-top"><span>{{ $label }}</span><span class="metric-icon {{ $color }}"><x-icon :name="$icon" size="17"/></span></div><div class="metric-value">{{ str_pad((string)$value,2,'0',STR_PAD_LEFT) }}</div><div class="metric-caption">{{ $caption }}</div></div>
@endforeach
</div>
<div class="dashboard-split">
<section class="panel clock-panel"><div class="panel-header"><div><h2>Make today count.</h2><p>Your daily check-in and check-out</p></div><x-icon name="clock"/></div>
<div class="clock-face"><div class="clock-time" data-clock>{{ now('Asia/Karachi')->format('H:i:s') }}</div><div class="clock-date">{{ now('Asia/Karachi')->format('l, d F Y') }} · Pakistan Standard Time</div><div class="clock-status"><span class="status-pill status-{{ strtolower($status) }}">{{ $status }}</span></div>
@if(!$employee->active)<div class="alert alert-warning">Your profile is inactive. You can view your records, but cannot submit attendance or new leave.</div>
@elseif(!$workingDay)<p class="role-caption">Today is a non-working day. Enjoy your time away.</p>
@elseif(!$attendance)
@if($status==='Leave')<p class="role-caption">You have approved leave today. Checking in will record attendance instead.</p>@endif
<div class="clock-actions"><form method="post" action="{{ route('checkin') }}">@csrf<button class="btn btn-primary"><x-icon name="check" size="17"/>Check in</button></form></div>
@elseif(!$attendance->check_out_at)<div class="clock-actions"><form method="post" action="{{ route('checkout') }}">@csrf<button class="btn btn-primary"><x-icon name="logout" size="17"/>Check out</button></form></div>
@else<p class="role-caption mb-0"><x-icon name="check" size="16"/> You are checked out for today. See you next workday.</p>@endif
<div class="time-pair"><div><span>CHECK-IN</span><strong>{{ $attendance?->check_in_at??'—' }}</strong></div><div><span>CHECK-OUT</span><strong>{{ $attendance?->check_out_at??'—' }}</strong></div></div>
</div></section>
<section class="panel"><div class="panel-header"><div><h2>Your workspace</h2><p>{{ $employee->department->name }}</p></div><x-icon name="user"/></div><div class="schedule-card">
<div class="schedule-row"><span>Employee ID</span><strong>{{ $employee->employee_code }}</strong></div><div class="schedule-row"><span>Office starts</span><strong>{{ config('attendance.office_start') }}</strong></div><div class="schedule-row"><span>Grace period</span><strong>{{ config('attendance.grace_minutes') }} minutes</strong></div><div class="schedule-row"><span>Account status</span><strong>{{ $employee->active?'Active':'Inactive' }}</strong></div>
<a class="action-row" href="{{ route('attendance.mine') }}"><span class="action-icon"><x-icon name="chart" size="18"/></span><span><strong>Explore your attendance</strong><small>Daily history and monthly summary</small></span><x-icon name="chevron" size="15"/></a>
<a class="action-row" href="{{ route('leaves.index') }}"><span class="action-icon"><x-icon name="calendar" size="18"/></span><span><strong>Plan some time away</strong><small>View or submit a leave request</small></span><x-icon name="chevron" size="15"/></a>
</div></section>
</div>
<section class="panel"><div class="panel-header"><div><h2>Your recent attendance</h2><p>Your latest recorded working days</p></div><a class="text-link" href="{{ route('attendance.mine') }}">View history<x-icon name="arrow" size="15"/></a></div>
<div class="table-responsive"><table class="table"><thead><tr><th>Date</th><th>Check-in</th><th>Check-out</th><th>Status</th></tr></thead><tbody>
@forelse($recent as $entry)<tr><td>{{ $entry->attendance_date->format('D, d M Y') }}</td><td>{{ $entry->check_in_at??'—' }}</td><td>{{ $entry->check_out_at??'—' }}</td><td><span class="status-pill status-{{ $entry->status }}">{{ ucfirst($entry->status) }}</span></td></tr>
@empty<tr><td colspan="4"><div class="empty-state"><x-icon name="clock" size="28"/><strong>A fresh start.</strong><p>Your recorded attendance will appear here after your first check-in.</p></div></td></tr>@endforelse
</tbody></table></div></section>
@endif
@endsection
