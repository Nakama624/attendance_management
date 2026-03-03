@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
<div class="content">
    @include('components.login-form', [
        'title'  => 'ログイン',
        'btn'  => 'ログインする',
    ])
</div>
@endsection