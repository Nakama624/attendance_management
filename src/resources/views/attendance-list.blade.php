@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="list">
        <h1 class="list-ttl">勤怠一覧</h1>
        <!-- バー -->
        <div class="attendance-month">
            <div class="last-month">
                <i class="fa-solid fa-arrow-left"></i>
                前月
            </div>
            <div class="calendar-select">
                <i class="fa-chisel fa-regular fa-calendar"></i>
                2023/06
            </div>
            <div class="next-month">
                翌月
                <i class="fa-solid fa-arrow-right"></i>
            </div>
        </div>

        <!-- 一覧 -->
        <table class="attendance-table">
            <tr class="attendance-list__row">
                <th class="attendance-date">日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
            <tr class="attendance-list__row">
                <td>06/01(木)</td>
                <td>09：00</td>
                <td>18：00</td>
                <td>1：00</td>
                <td>8：00</td>
                <td>
                    <a href="/login" class="attendance-detail">詳細</a>
                </td>
            </tr>
            <tr class="attendance-list__row">
                <td>06/01(木)</td>
                <td>09：00</td>
                <td>18：00</td>
                <td>1：00</td>
                <td>8：00</td>
                <td>
                    <a href="/login" class="attendance-detail">詳細</a>
                </td>
            </tr>
        </table>
    </div>







</div>
@endsection