@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="list">
        <h1 class="list-ttl">{{ $targetUser->name }}さんの勤怠</h1>

        <div class="attendance-month">
            <div class="last-month">
                <a href="{{ route('admin.attendance.staff', [
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
                <a href="{{ route('admin.attendance.staff', [
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

        <div class="csv">
            <a class="csv-btn"
                href="{{ route('admin.attendance.staff.export', [
                    'id' => $targetUser->id,
                    'month' => $currentMonth->format('Y-m')
                ]) }}">
                CSV出力
            </a>
        </div>
    </div>
</div>
@endsection
