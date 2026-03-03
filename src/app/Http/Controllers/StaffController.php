<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Application;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class StaffController extends Controller
{
    public function staffList(){
        $users = User::all();

        return view('admin.admin-staff-list', compact('users'));
    }

    public function staffAttendanceList(Request $request, $id = null){
        $month = $request->input('month');
        $base = $month
            ? Carbon::createFromFormat('Y-m', $month)->startOfMonth()
            : now()->startOfMonth();

        $start = $base->copy()->startOfMonth();
        $end   = $base->copy()->endOfMonth();

        // 分→「H：MM」変換（表示用）
        $fmtMinutes = function (?int $m): string {
            if (!$m) return '';
            $h = intdiv($m, 60);
            $min = $m % 60;
            return $h . ':' . str_pad((string)$min, 2, '0', STR_PAD_LEFT);
        };

        $targetUserId = $id ? (int) $id : Auth::id();

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $targetUserId)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        // AttendanceのID一覧
        $attendanceIds = $attendances->pluck('id')->filter()->all();

        // attendance_idごとに「最新の申請」だけを1件にまとめたMapを作る
        $latestAppsByAttendanceId = Application::whereIn('attendance_id', $attendanceIds)
            ->orderBy('attendance_id')
            ->orderByDesc('created_at') // 最新
            ->get()
            ->groupBy('attendance_id')
            ->map(fn ($g) => $g->first()); // 各attendance_idの先頭=最新

        // 日付検索
        $attendancesByDate = $attendances->keyBy(fn ($a) => $a->attendance_date->toDateString());

        $days = collect(CarbonPeriod::create($start, $end))
            ->map(function (Carbon $date) use ($attendancesByDate, $latestAppsByAttendanceId, $fmtMinutes) {

                $key = $date->toDateString();
                $attendance = $attendancesByDate->get($key);

                $breakMinutes = 0;
                $workMinutes  = 0;

                $startAt  = $attendance?->attendance_start_at;
                $finishAt = $attendance?->attendance_finish_at;

                if ($attendance) {
                    // 休憩合計（複数休憩対応）
                    $breakMinutes = $attendance->breakTimes->sum(function ($b) {
                        if (!$b->break_time_start_at || !$b->break_time_finish_at) return 0;
                        return Carbon::parse($b->break_time_finish_at)
                            ->diffInMinutes(Carbon::parse($b->break_time_start_at));
                    });

                    // 勤務合計（出勤〜退勤 − 休憩）
                    if ($startAt && $finishAt) {
                        $workMinutes = Carbon::parse($finishAt)->diffInMinutes(Carbon::parse($startAt));
                        $workMinutes = max(0, $workMinutes - $breakMinutes);
                    }
                }

                $latestApp = $attendance ? ($latestAppsByAttendanceId->get($attendance->id) ?? null) : null;

                return [
                    'date'        => $date->toDateString(),
                    'dateLabel'   => $date->isoFormat('MM/DD(ddd)'),
                    'startLabel'  => $startAt ? Carbon::parse($startAt)->format('H:i') : '',
                    'finishLabel' => $finishAt ? Carbon::parse($finishAt)->format('H:i') : '',
                    'breakLabel'  => $fmtMinutes($breakMinutes),
                    'workLabel'   => $fmtMinutes($workMinutes),
                    'attendanceId'=> $attendance?->id,
                    'applicationId'      => $latestApp?->id,
                    'applicationStatus'  => $latestApp?->application_status_id,
                ];
            });

            $items = $days->map(function ($d) {
                return [
                    'firstCol'    => $d['dateLabel'],
                    'startLabel'  => $d['startLabel'],
                    'finishLabel' => $d['finishLabel'],
                    'breakLabel'  => $d['breakLabel'],
                    'workLabel'   => $d['workLabel'],
                    'detailUrl'   => ($d['applicationId'] && $d['applicationStatus'] === 1)
                        ? url('/attendance/pending/'.$d['applicationId'].'?date='.$d['date'])
                        : (
                            $d['attendanceId']
                                ? url('/attendance/detail/'.$d['attendanceId'].'?date='.$d['date'])
                                : url('/attendance/detail?date='.$d['date'])
                        ),
                ];
            });

        // 静的解析ツールで使用
        /** @var view-string $view */
        $view = 'staff.attendance-list';

        return view($view, [
            'title' => '勤怠一覧',
            'centerLabel' => $base->format('Y/m'),
            'prevUrl' => route('attendance.list', ['month' => $base->copy()->subMonth()->format('Y-m')]),
            'prevText' => '前月',
            'nextUrl' => route('attendance.list', ['month' => $base->copy()->addMonth()->format('Y-m')]),
            'nextText' => '翌月',
            'firstColHeader' => '日付',
            'items' => $items,
            'days' => $days,
            'currentMonth' => $base,
            'prevMonth' => $base->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $base->copy()->addMonth()->format('Y-m'),
        ]);
    }
}
