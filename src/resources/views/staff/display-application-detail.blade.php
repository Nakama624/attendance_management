@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="list">
        <h1 class="list-ttl">勤怠詳細</h1>
        <table class="attendance-table">
            <tr class="attendance-list__row">
                <th>名前</th>
                <td><div class="cell cell--center">{{ $userName }}</div></td>
            </tr>

            <tr class="attendance-list__row">
                <th>日付</th>
                <td>
                    <div class="cell cell--date">
                        <span>{{ $date?->format('Y年') }}</span>
                        <span>{{ $date?->format('n月j日') }}</span>
                    </div>
                </td>
            </tr>

            <tr class="attendance-list__row">
                <th>出勤・退勤</th>
                <td>
                    <div class="cell display-cell__time">
                        <span class="attendance-list__display">
                            {{ $start }}
                        </span>
                        <span class="sep-display">〜</span>
                        <span class="attendance-list__display">
                            {{ $finish }}
                        </span>
                    </div>
                </td>
            </tr>

            @foreach ($breakRows as $i => $row)
                <tr class="attendance-list__row">
                    <th>休憩{{ $i === 0 ? '' : $i + 1 }}</th>
                    <td>
                    <div class="cell display-cell__time">
                        <span class="attendance-list__display">{{ $row['start'] }}</span>
                        <span class="sep-display">〜</span>
                        <span class="attendance-list__display">{{ $row['finish'] }}</span>
                    </div>
                    </td>
                </tr>
            @endforeach

            <tr class="attendance-list__textarea-row">
                <th>備考</th>
                <td>
                    <span class="attendance-list__display--textarea">{{ $remarks }}</span>
                </td>
            </tr>
        </table>

        @if($application->application_status_id === 1)
            <div class="staff-pending-application">
                * 承認待ちのため修正はできません。
            </div>
        @endif
    </div>
</div>
@endsection
