@extends('layouts.app')
@section('title','Login')
@section('content')
<div class="row justify-content-center"><div class="col-md-5"><div class="card shadow-sm"><div class="card-body p-4">
<h1 class="h4 mb-3">Sign in</h1>
<form method="post" action="{{ route('login.submit') }}">@csrf
<label class="form-label" for="email">Email</label><input id="email" type="email" name="email" class="form-control mb-3" value="{{ old('email') }}" required autofocus>
<label class="form-label" for="password">Password</label><input id="password" type="password" name="password" class="form-control mb-3" required>
<button class="btn btn-primary w-100">Log in</button></form>
</div></div></div></div>
@endsection
