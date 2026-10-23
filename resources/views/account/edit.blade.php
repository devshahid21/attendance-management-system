@extends('layouts.app')
@section('title','Account settings')
@section('content')
<div class="page-heading"><div><div class="eyebrow">YOUR ACCOUNT</div><h1>Account settings</h1><p>Keep your sign-in details secure and up to date.</p></div></div>
<div class="account-grid">
<section class="panel"><div class="profile-banner"><span class="avatar">{{ mb_strtoupper(mb_substr($user->name,0,1)) }}</span><div><h2>{{ $user->name }}</h2><p>{{ ['superadmin'=>'Super Administrator','admin'=>'Administrator','employee'=>'Employee account'][$user->role] ?? 'Account' }}</p></div></div>
<div class="panel-body"><div class="schedule-row"><span>Work email</span><strong style="overflow-wrap:anywhere">{{ $user->email }}</strong></div><div class="schedule-row"><span>Access level</span><strong>{{ ucfirst($user->role) }}</strong></div><p class="form-text mt-3">Your administrator manages your name, email and employee profile.</p></div></section>
<section class="panel"><div class="panel-header"><div><h2>Change password</h2><p>Use a password you do not use anywhere else.</p></div><x-icon name="lock"/></div>
<div class="panel-body"><form method="post" action="{{ route('account.password') }}">@csrf @method('PUT')
<div class="mb-3"><label class="form-label" for="current-password">Current password</label><input id="current-password" class="form-control" type="password" name="current_password" autocomplete="current-password" required></div>
<div class="mb-3"><label class="form-label" for="new-password">New password</label><input id="new-password" class="form-control" type="password" name="password" autocomplete="new-password" minlength="8" required><div class="form-text">At least 8 characters. Choose a different password from your current one.</div></div>
<div class="mb-4"><label class="form-label" for="confirm-password">Confirm new password</label><input id="confirm-password" class="form-control" type="password" name="password_confirmation" autocomplete="new-password" required></div>
<button class="btn btn-primary"><x-icon name="check" size="16"/>Update password</button>
</form></div></section>
</div>
@endsection
