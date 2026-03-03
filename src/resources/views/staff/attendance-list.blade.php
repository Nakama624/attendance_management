@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="list">
        <h1 class="list-ttl">勤怠一覧</h1>

        <div class="attendance-month">
            <div class="last-month">
                <a href="{{ route('attendance.list', [
                    'id' => $targetUser->id,
                    'month' => $currentMonth->copy()->subMonth()->format('Y-m')
                ]) }}">
                    <img src="{{ asset('images/left.svg') }}" alt="left">
                    <span class="left">前月</span>
                </a>
            </div>

            <div class="calendar-select">
                <img src="{{ asset('images/calendar.svg') }}" alt="calendar">
                {{ $currentMonth->format('Y/m') }}
            </div>

            <div class="next-month">
                <a href="{{ route('attendance.list', [
                    'id' => $targetUser->id,
                    'month' => $currentMonth->copy()->addMonth()->format('Y-m')
                ]) }}">
                    <span class="right">翌月</span>
                    <img src="{{ asset('images/right.svg') }}" alt="right">
                </a>
            </div>
        </div>

        <x-table
            first-col-header="日付"
            :attendances="$attendances"
        />
    </div>
</div>
@endsection
