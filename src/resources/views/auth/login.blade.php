@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
<div class="content">
    <form action="/login" class="login-form" method="post">
        @csrf
        <!-- <h1 class="content-title">ログイン</h1> -->
        <h1 class="content-title">管理者ログイン</h1>
        <!-- メールアドレス -->
        <div class="form-input">
            <label for="email" class="form__label--item">メールアドレス</label>
            <input id="email" type="text" name="email" class="form__input--item"
            value="{{ old('email') }}" />
                <div class="form__error">
                    @error('email')
                    {   { $message }}
                    @enderror
                </div>
        </div>
        <!-- パスワード -->
        <div class="form-input">
            <label for="password" class="form__label--item">パスワード</label>
            <input id="password" type="password" name="password" class="form__input--item"/>
            <div class="form__error">
                @error('password')
                {{ $message }}
                @enderror
            </div>
        </div>
        <div class="form__button">
            <!-- <button class="form__button-submit" type="submit">ログイン</button> -->
            <button class="form__button-submit" type="submit">管理者ログイン</button>
            <a class="form__button-tran" href="/register">会員登録はこちら</a>
        </div>
    </form>
</div>
@endsection