@extends('layouts.app')
@section('title','Roles & access')
@section('content')
<div class="page-heading"><div><div class="eyebrow">WORKSPACE ACCESS</div><h1>The right access for every role.</h1><p>Three fixed roles keep team records organized and salary details private.</p></div><span class="status-pill status-present">Role-based access</span></div>
<section class="panel mb-4"><div class="panel-body"><h2 class="h5">Super Administrator</h2><p class="role-caption mb-0">Inherits administrator access and manages private monthly base salary records. Ordinary administrators and employees cannot view or change salaries. Roles are fixed; this page is an access overview.</p></div></section><div class="account-grid mb-4"><section class="panel"><div class="panel-body"><span class="metric-icon mb-3"><x-icon name="shield"/></span><h2 class="h5">Administrator</h2><p class="role-caption mb-0">Manages departments, employee accounts, attendance corrections, leave decisions and team reports. Employee accounts created here always receive the Employee role.</p></div></section><section class="panel"><div class="panel-body"><span class="metric-icon blue mb-3"><x-icon name="user"/></span><h2 class="h5">Employee</h2><p class="role-caption mb-0">Views only their own profile, attendance and leave. Active employees can check in, check out and request leave. Deactivated employees retain read-only history access.</p></div></section></div>
<section class="panel"><div class="panel-header"><div><h2>Permissions at a glance</h2><p>These access rules are fixed and enforced by the application.</p></div><x-icon name="lock"/></div>
<div class="table-responsive"><table class="table"><thead><tr><th>Capability</th><th>Super Administrator</th><th>Administrator</th><th>Employee</th></tr></thead><tbody>
@foreach([
['Company dashboard','Yes','Own dashboard'],
['Departments & employee accounts','Manage','No access'],
['Daily attendance records','View all & correct','Own records only'],
['Check-in & check-out','No employee actions','Own account, when active'],
['Leave requests','Review all','Request & view own'],
['Monthly reports & CSV','View & export all','Own history only'],
['Roles & access overview','View','No access'],
['Password change','Own password','Own password'],
['Monthly base salaries','No access','No access'],
] as [$label,$admin,$employee])
<tr><td><strong>{{ $label }}</strong></td><td class="permission-yes">{{ $label==='Monthly base salaries'?'Manage':$admin }}</td><td class="permission-yes">{{ $admin }}</td><td class="{{ $employee==='No access'?'permission-no':'permission-yes' }}">{{ $employee }}</td></tr>
@endforeach
</tbody></table></div></section>
@endsection
