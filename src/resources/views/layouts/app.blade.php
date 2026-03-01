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
            <img src="{{ asset('images/COACHTECH.png') }}" alt="COACHTECH" class="header-left__logo">
        </div>

        @if(Auth::check())
        <div class="header-nav__item">
            @admin
                <a class="header-nav__button" href="/admin/attendance/list">勤怠一覧</a>
                <a class="header-nav__button" href="/admin/staff/list">スタッフ一覧</a>
                <a class="header-nav__button" href="/stamp_correction_request/list?page=pending">申請一覧</a>
                <form action="/logout" method="post">
                    @csrf
                    <input type="hidden" name="is_admin_logout" value="1">
                    <button class="header-nav__button">ログアウト</button>
                </form>
            @else
                @if(isset($attendance) && $attendance->attendance_status_id === 4 && request()->routeIs('attendance.stamping'))
                    <a class="header-nav__button" href="/attendance/list">今月の勤怠一覧</a>
                    <a class="header-nav__button" href="/stamp_correction_request/list?page=pending">申請一覧</a>
                @else
                    <a class="header-nav__button" href="/attendance">勤怠</a>
                    <a class="header-nav__button" href="/attendance/list">勤怠一覧</a>
                    <a class="header-nav__button" href="/stamp_correction_request/list?page=pending">申請</a>
                @endif
                <form action="/logout" method="post">
                    @csrf
                    <button class="header-nav__button">ログアウト</button>
                </form>
            @endadmin
        </div>
        @endif
    </header>
    <main>
        @yield('content')
    </main>
</body>

</html>
