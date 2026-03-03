@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@section('content')
<div class="content">
    <form action="/register" class="register-form" method="post">
        @csrf
        <h1 class="content-title">会員登録</h1>
        <!-- ユーザー名 -->
        <div class="form-input">
            <label for="name" class="form__label--item">ユーザー名</label>
            <input id="name" type="text" name="name" class="form__input--item" value="{{ old('name') }}" />
            <div class="form__error">
                @error('name')
                {{ $message }}
                @enderror
            </div>
        </div>
        <!-- メールアドレス -->
        <div class="form-input">
            <label for="email" class="form__label--item">メールアドレス</label>
            <input id="email" type="text" name="email" class="form__input--item" value="{{ old('email') }}" />
            <div class="form__error">
                @error('email')
                {{ $message }}
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
        <!-- 確認用パスワード -->
        <div class="form-input">
            <label for="password_confirmation" class="form__label--item">確認用パスワード</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="form__input--item"/>
            <div class="form__error">
                @error('password_confirmation')
                {{ $message }}
                @enderror
            </div>
        </div>
        <!-- ボタン -->
        <div class="form__button">
            <button class="form__button-submit" type="submit">登録する</button>
            <a class="form__button-tran" href="/login">ログインはこちら</a>
        </div>
    </form>
</div>
@endsection