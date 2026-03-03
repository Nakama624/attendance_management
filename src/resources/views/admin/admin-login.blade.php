@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
<div class="content">
    @include('components.login-form', [
        'title'  => '管理者ログイン',
        'btn'  => '管理者ログイン',
        'isAdmin' => true,
    ])
</div>
@endsection