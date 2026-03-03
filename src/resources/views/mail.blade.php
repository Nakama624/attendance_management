@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/mail.css') }}">
@endsection

@section('content')
<div class="content">
    @if (auth()->user()->hasVerifiedEmail())

        <p class="message">メール認証は完了しています。</p>

    @else
        @if (session('status') === 'verification-link-sent')
            <p class="message">認証メールを送信しました。</p>
        @else
            <p class="message">登録していただいたメールアドレスに認証メールを送付しました。</p>
            <p class="message">メール認証を完了してください。</p>

            <div class="auth">
                <a href="/dev/mailhog/open" target="_blank" rel="noopener noreferrer" class="auth-btn">
                    認証はこちらから
                </a>
            </div>
        @endif

        <form method="POST" action="/email/verification-notification" class="resent">
            @csrf
            <button type="submit" class="resent-btn">
                認証メールを再送する
            </button>
        </form>
    @endif

</div>
@endsection
