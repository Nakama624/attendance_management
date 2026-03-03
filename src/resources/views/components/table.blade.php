<table class="attendance-table">
    <tr class="attendance-list__row">
        <th class="attendance-date">{{ $firstColHeader }}</th>
        <th>出勤</th>
        <th>退勤</th>
        <th>休憩</th>
        <th>合計</th>
        <th>詳細</th>
    </tr>

    @foreach($attendances as $attendance)
        <tr class="attendance-list__row">
            <td>{{ $attendance['firstCol'] }}</td>
            <td>{{ $attendance['startLabel'] }}</td>
            <td>{{ $attendance['finishLabel'] }}</td>
            <td>{{ $attendance['breakLabel'] }}</td>
            <td>{{ $attendance['workLabel'] }}</td>
            <td>
                @if(!empty($attendance['startLabel']))
                    <a href="{{ $attendance['detailUrl'] }}" class="attendance-detail">詳細</a>
                @else
                    <span class="attendance-detail attendance-detail--disabled">詳細</span>
                @endif
            </td>
        </tr>
    @endforeach
</table>
