@extends('layouts.app')
@section('title','Access restricted')
@section('content')
<section class="panel" style="max-width:620px;margin:40px auto"><div class="panel-body" style="padding:45px">
<div class="eyebrow">403 · WORKSPACE</div><h1 style="font-size:28px;letter-spacing:-.7px">Access restricted</h1>
<p class="role-caption mt-3 mb-4">This page is not available for your role. Your own workspace is ready when you are.</p><a class="btn btn-primary" href="{{ auth()->check()?route('dashboard'):route('login') }}">Back to {{ auth()->check()?'overview':'sign in' }}<x-icon name="arrow" size="16"/></a>
</div></section>
@endsection
