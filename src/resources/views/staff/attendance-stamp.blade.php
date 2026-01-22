@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamping.css') }}">
@endsection

@section('content')
<div class="content">
    <form action="/stamp" class="attendance-stamp-form" method="post">
        <span class="attendance-status">勤務中</span>
        <h1 class="attendance-date">2023年6月1日(木)</h1>
        <h2 class="attendance-time">08:00</h2>

        <div class="form__button">
            <button class="attendance__btn--black" type="submit">出勤</button>
            <button class="attendance__btn--black" type="submit">退勤</button>
            <button class="attendance__btn--white" type="submit">休憩入</button>
            <button class="attendance__btn--white" type="submit">休憩戻</button>
        </div>
        <p class="work-finish-message">お疲れ様でした。</p>
    </form>
</div>
@endsection