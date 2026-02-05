<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Management</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/common.css') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @yield('css')
</head>
    <body>
        <header class="header">
            <!-- ロゴ -->
            <div class="header-left">
                <a href="/">
                    <img src="{{ asset('images/COACHTECH.png') }}" alt="COACHTECH" class="header-left__logo">
                </a>
            </div>
            <!-- "ログイン"または"会員登録"画面の場合は、検索＆ボタンは表示しない -->
            @if (request()->path() == 'login' || request()->path() == 'register')
            <div class="header__btn">
            </div>
            @else
            <!-- ボタン群 -->
            <div class="header-right">
                @if (Auth::check())
                <!-- かつ一般スタッフの場合 -->
                <div class="header-nav__item">
                    <a class="header-nav__button" href="/attendance">勤怠</a>
                    <a class="header-nav__button" href="/attendance/list">勤怠一覧</a>
                    <a class="header-nav__button"href="/stamp_correction_request/list">申請</a>
                    <form action="/logout" method="post">
                        @csrf
                        <button class="header-nav__button">ログアウト</button>
                    </form>
                </div>
                <!-- 承認済みかつ管理者の場合 -->





                @else
                    <div class="header-nav__item">
                        <a href="/login" class="header-nav__button">ログイン</a>
                    </div>
                @endif
            </div>
            @endif
        </header>
        <main>
            @yield('content')
        </main>
    </body>
</html>
