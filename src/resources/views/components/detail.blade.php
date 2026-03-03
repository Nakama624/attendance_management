<form method="POST" action="{{ $action }}">
    @csrf
    <table class="attendance-table">
        <tr class="attendance-list__row">
        <th>名前</th>
        <td><div class="cell cell--center">{{ $attendance->user->name }}</div></td>
        </tr>

        <tr class="attendance-list__row">
            <th>日付</th>
            <td>
                <div class="cell cell--date">
                    <span>{{ $attendance->attendance_date->format('Y年') }}</span>
                    <span>{{ $attendance->attendance_date->format('n月j日') }}</span>
                    <input type="hidden"
                        name="attendance_date"
                        value="{{ $attendance->attendance_date->format('Y-m-d') }}">
                </div>
            </td>
        </tr>

        <tr class="attendance-list__row">
            <th>出勤・退勤</th>
            <td>
                <div class="cell cell__time">
                    <div class="cell-input">
                        <input class="attendance-list__input"
                            name="attendance_start_at"
                            value="{{ old('attendance_start_at',
                                $attendance->attendance_start_at
                                    ? $attendance->attendance_start_at->format('H:i')
                                    : ''
                            ) }}">
                        <div class="form__error">
                            @error('attendance_start_at')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>

                    <span class="sep">〜</span>
                    <div class="cell-input">
                        <input class="attendance-list__input"
                            name="attendance_finish_at"
                            value="{{ old('attendance_finish_at',
                                $attendance->attendance_finish_at
                                    ? $attendance->attendance_finish_at->format('H:i')
                                    : ''
                            ) }}">
                        <div class="form__error">
                            @error('attendance_finish_at')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </div>
            </td>
        </tr>

        @foreach ($breakRows as $i => $row)
        <tr class="attendance-list__row">
            <th>休憩{{ $i === 0 ? '' : $i + 1 }}</th>
            <td>
                <div class="cell cell__time">
                    <!-- 休憩開始 -->
                    <div class="cell-input">
                        <input class="attendance-list__input"
                            name="breaks[{{ $i }}][start]"
                            value="{{ old('breaks.'.$i.'.start', $row['start'] ?? '') }}">
                        <div class="form__error">
                            @error('breaks.'.$i.'.start')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>

                    <span class="sep">〜</span>

                    <!-- 休憩終了 -->
                    <div class="cell-input">
                        <input class="attendance-list__input"
                            name="breaks[{{ $i }}][finish]"
                            value="{{ old('breaks.'.$i.'.finish', $row['finish'] ?? '') }}">
                        <div class="form__error">
                            @error('breaks.'.$i.'.finish')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>

                    <input type="hidden" name="breaks[{{ $i }}][id]" value="{{ $row['id'] }}">
                </div>
            </td>
        </tr>
        @endforeach

        <tr class="attendance-list__textarea-row">
            <th>備考</th>
            <td>
                <textarea class="attendance-list__textarea" name="remarks">{{ old('remarks', $attendance->remarks ?? '') }}</textarea>
                <div class="form__error">
                    @error('remarks')
                        {{ $message }}
                    @enderror
                </div>
            </td>
        </tr>
    </table>

    <div class="attendance-modify">
        <button type="submit" class="attendance-modify__btn">{{ $submitText ?? '修正' }}</button>
    </div>
</form>
