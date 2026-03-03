@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="list">
        <h1 class="list-ttl">勤怠詳細</h1>

        <x-detail
            :attendance="$attendance"
            :break-rows="$breakRows"
            :action="route('attendance.modify', $attendance->id ?? null)"
            submit-text="修正"
        />

    </div>
</div>
@endsection
