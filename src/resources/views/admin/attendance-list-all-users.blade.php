@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="list">
        <h1 class="list-ttl">{{ $date->isoFormat('YYYY年M月D日') }}の勤怠</h1>

        <div class="attendance-month">
            <div class="last-month">
                <a href="{{ route('admin.attendance.list', [
                    'day' => $date->copy()->subDay()->format('Y-m-d')
                ]) }}">
                    <img src="{{ asset('images/left.svg') }}" alt="left">
                    <span class="left">前日</span>
                </a>
            </div>

            <div class="calendar-select">
                <img src="{{ asset('images/calendar.svg') }}" alt="calendar">
                {{ $date->format('Y/m/d') }}
            </div>

            <div class="next-month">
                <a href="{{ route('admin.attendance.list', [
                    'day' => $date->copy()->addDay()->format('Y-m-d')
                ]) }}">
                    <span class="right">翌日</span>
                    <img src="{{ asset('images/right.svg') }}" alt="right">
                </a>
            </div>
        </div>

        <x-table
            first-col-header="名前"
            :attendances="$attendances"
        />
    </div>
</div>
@endsection
