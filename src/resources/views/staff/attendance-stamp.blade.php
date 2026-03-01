@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamping.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="attendance-stamp-form">
        <span class="attendance-status">{{ optional($attendance->attendanceStatus)->status_name }}</span>
        <p class="attendance-date">
            {{ now()->format('Y年n月j日') }}({{ ['日','月','火','水','木','金','土'][now()->dayOfWeek] }})
        </p>
        <h1 class="attendance-time">{{ now()->format('H:i') }}</h1>
    </div>
    @if ($attendance->attendance_status_id == 1)
        <form action="{{ url('/attendance/start') }}" method="post" class="attendance__btn">
            @csrf
            <button class="attendance__btn--black" type="submit">出勤</button>
        </form>
    @elseif ($attendance->attendance_status_id == 2)
        <div class="attendance-finish__breaktime-start--btn">
            <form action="{{ url('/attendance/finish') }}" method="post">
                @method('PATCH')
                @csrf
                <button class="attendance__btn--black" type="submit">退勤</button>
            </form>
            <form action="{{ url('/attendance/start_break_time') }}" method="post">
                @csrf
                <button class="attendance__btn--white" type="submit">休憩入</button>
            </form>
        </div>
    @elseif ($attendance->attendance_status_id == 3)
        <form action="{{ url('/attendance/finish_break_time') }}" method="post" class="attendance__btn">
            @method('PATCH')
            @csrf
            <button class="attendance__btn--white" type="submit">休憩戻</button>
        </form>
    @elseif ($attendance->attendance_status_id == 4)
        <p class="work-finish-message">お疲れ様でした。</p>
    @endif
</div>
@endsection