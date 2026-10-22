@extends('layouts.app')
@section('title','We could not find that page.')
@section('content')
<section class="panel" style="max-width:620px;margin:40px auto"><div class="panel-body" style="padding:45px">
<div class="eyebrow">404 · WORKSPACE</div><h1 style="font-size:28px;letter-spacing:-.7px">We could not find that page.</h1>
<p class="role-caption mt-3 mb-4">The page may have moved, or the link may be incorrect.</p><a class="btn btn-primary" href="{{ auth()->check()?route('dashboard'):route('login') }}">Back to {{ auth()->check()?'overview':'sign in' }}<x-icon name="arrow" size="16"/></a>
</div></section>
@endsection
