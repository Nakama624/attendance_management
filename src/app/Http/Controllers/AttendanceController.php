<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Application;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AttendanceController extends Controller
{
    public function attendanceList(Request $request, $id = null){
        $isAdminRoute = $request->routeIs('admin.attendance.staff');
        $targetUserId = $isAdminRoute ? (int)$id : Auth::id();

        $targetUser = $isAdminRoute ? User::findOrFail($targetUserId) : Auth::user();

        $month = $request->input('month');
        $base = $month
            ? Carbon::createFromFormat('Y-m', $month)->startOfMonth()
            : now()->startOfMonth();

        $start = $base->copy()->startOfMonth();
        $end   = $base->copy()->endOfMonth();

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $targetUserId)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        // AttendanceのID一覧
        $attendanceIds = $attendances->pluck('id')->filter()->all();

        // attendance_idごとに「最新の申請」だけを1件にまとめたMap
        $latestAppsByAttendanceId = Application::whereIn('attendance_id', $attendanceIds)
            ->orderBy('attendance_id')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('attendance_id')
            ->map(fn ($g) => $g->first());

        $attendancesByDate = $attendances->keyBy(fn ($a) => $a->attendance_date->toDateString());

        $days = collect(CarbonPeriod::create($start, $end))
            ->map(function (Carbon $date) use ($attendancesByDate, $latestAppsByAttendanceId) {

            $key = $date->toDateString();
            $attendance = $attendancesByDate->get($key);

            $latestApp = $attendance ? ($latestAppsByAttendanceId->get($attendance->id) ?? null) : null;

            return [
                'date'        => $date->toDateString(),
                'dateLabel'   => $date->isoFormat('MM/DD(ddd)'),
                'startLabel'  => $attendance?->start_label ?? '',
                'finishLabel' => $attendance?->finish_label ?? '',
                'breakLabel'  => $attendance?->break_label ?? '',
                'workLabel'   => $attendance?->work_label ?? '',
                'attendanceId'=> $attendance?->id,
                'applicationId'      => $latestApp?->id,
                'applicationStatus'  => $latestApp?->application_status_id,
            ];
        });

        // 一覧に表示するデータ
        $attendancesForView = $days->map(function ($d) {
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

        return view('staff.attendance-list', [
            'firstColHeader' => '日付',
            'attendances' => $attendancesForView,

            'days' => $days,
            'currentMonth' => $base,

            'targetUser' => $targetUser,
        ]);
    }

    public function attendanceDetail(Request $request, $attendance_id = null){
        // attendance_id が無い場合：表示用のダミー attendance を作る
        if (empty($attendance_id)) {

            $selectedDate = $request->query('date');
            // ダミー attendance（Bladeが参照するプロパティを全部持たせる）
            $attendance = new \stdClass();
            $attendance->id = null;

            $attendance->user = (object)[
                'name' => Auth::user()->name ?? '',
            ];

            $attendance->attendance_date = $selectedDate
                ? Carbon::parse($selectedDate)
                : Carbon::now();

            $attendance->attendance_start_at = null;
            $attendance->attendance_finish_at = null;

            $attendance->remarks = '';

            $breakRows = collect([[
                'id' => '',
                'start' => '',
                'finish' => '',
            ]]);

            $action = '';

            return view('staff.attendance-detail', compact('attendance', 'breakRows', 'action'));
        }

        // attendance を取得
        $attendance = Attendance::with('user', 'breakTimes')->findOrFail($attendance_id);

        // 直近申請が承認待ちなら申請詳細へ
        $latestApplication = Application::where('attendance_id', $attendance->id)->latest()->first();
        if ($latestApplication && $latestApplication->application_status_id === 1) {
            return redirect()->route('stamp_correction_request.approved', $latestApplication->id);
        }

        $breakRows = $attendance->breakTimes
            ->map(function ($break) {
                return [
                    'id'     => $break->id,
                    'start'  => $break->break_time_start_at ? substr($break->break_time_start_at, 0, 5) : '',
                    'finish' => $break->break_time_finish_at ? substr($break->break_time_finish_at, 0, 5) : '',
                ];
            })
            ->values();

        $breakRows->push([
            'id' => '',
            'start' => '',
            'finish' => '',
        ]);

        $action = url('/attendance/modify/' . $attendance->id);

        return view('staff.attendance-detail', compact('attendance', 'breakRows', 'action'));
    }
}
