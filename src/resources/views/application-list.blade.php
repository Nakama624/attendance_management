@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/application-list.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="list">
        <h1 class="list-ttl">申請一覧</h1>
        <!-- バー -->
        <div class="form-change">
            <a href="/stamp_correction_request/list?page=pending"
                class="form-change__pending-approve  {{ request('page') === 'pending' ? 'is-active' : '' }}">
                承認待ち
            </a>
            <a href="/stamp_correction_request/list?page=approved"
                class="form-change__approved  {{ request('page') === 'approved' ? 'is-active' : '' }}">
                承認済み
            </a>
        </div>

        <!-- 一覧 -->
        <table class="attendance-table">
            <tr class="attendance-list__row">
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th class="attendance-list__reason">申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
            @foreach($applications as $application)
            <tr class="attendance-list__row">
                <td>{{ $application->applicationStatus->status_name }}</td>
                <td>{{ $application->user->name }}</td>
                <td>{{ $application->attendance?->attendance_date->format('Y/m/d') }}</td>
                <td>{{ $application->latestPending?->changes['remarks'] ?? '' }}</td>
                <td>{{ $application->created_at->format('Y/m/d') }}</td>
                <td>
                    <!-- 管理者 -->
                    @if(auth()->check() && auth()->user()->isManager())
                        <a href="{{ url('/stamp_correction_request/approve/'.$application->id) }}" class="attendance-detail">
                            詳細
                        </a>
                    <!-- 一般ユーザー -->
                    @else
                        <a href="{{ url('/attendance/pending/'.$application->id) }}" class="attendance-detail">
                            詳細
                        </a>
                    @endif
                </td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection