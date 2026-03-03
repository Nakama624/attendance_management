@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/application-list.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="list">
        <h1 class="list-ttl">スタッフ一覧</h1>

        <!-- 一覧 -->
        <table class="attendance-table">
            <tr class="attendance-list__row">
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
            @foreach($users as $user)
            <tr class="attendance-list__row">
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    <a href="{{ url('/admin/attendance/staff/'.$user['id']) }}" class="attendance-detail">
                        詳細
                    </a>
                </td>
            </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection