@extends('layouts.app')
@section('title','Your session needs a refresh.')
@section('content')
<section class="panel" style="max-width:620px;margin:40px auto"><div class="panel-body" style="padding:45px">
<div class="eyebrow">419 · WORKSPACE</div><h1 style="font-size:28px;letter-spacing:-.7px">Your session needs a refresh.</h1>
<p class="role-caption mt-3 mb-4">Refresh the previous page and try again. You may need to sign in again.</p><a class="btn btn-primary" href="{{ auth()->check()?route('dashboard'):route('login') }}">Back to {{ auth()->check()?'overview':'sign in' }}<x-icon name="arrow" size="16"/></a>
</div></section>
@endsection
