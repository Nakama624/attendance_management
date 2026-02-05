@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamping.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="attendance-stamp-form">
        @csrf
        <span class="attendance-status">{{ $attendance->attendanceStatuses->status_name }}</span>
        <h1 class="attendance-date">
            {{ now()->format('Y年n月j日') }}({{ ['日','月','火','水','木','金','土'][now()->dayOfWeek] }})
        </h1>
        <h2 class="attendance-time">{{ now()->format('H:i') }}</h2>
    </div>
    @if ($attendance->attendance_statuses_id == 1)
        <form action="{{ url('/attendance/start') }}" method="post" class="attendance__btn">
            @csrf
            <button class="attendance__btn--black" type="submit">出勤</button>
        </form>
    @elseif ($attendance->attendance_statuses_id == 2)
        <div class="attendance-finish__breaktime-start--btn">
            <form action="{{ url('/attendance/finish') }}" method="post">
                @csrf
                <button class="attendance__btn--black" type="submit">退勤</button>
            </form>
            <form action="{{ url('/attendance/start_break_time') }}" method="post">
                @csrf
                <button class="attendance__btn--white" type="submit">休憩入</button>
            </form>
        </div>
    @elseif ($attendance->attendance_statuses_id == 3)
        <form action="{{ url('/attendance/finish_break_time') }}" method="post" class="attendance__btn">
            @csrf
            <button class="attendance__btn--white" type="submit">休憩戻</button>
        </form>
            @elseif ($attendance->attendance_statuses_id == 4)
        <p class="work-finish-message">お疲れ様でした。</p>
    @endif
</div>
@endsection