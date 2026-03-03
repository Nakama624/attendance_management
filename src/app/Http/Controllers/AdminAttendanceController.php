<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Application;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAttendanceController extends Controller
{

    public function adminAttendanceList(Request $request, int $id){
        $targetUser = User::findOrFail($id);

        $month = $request->input('month');
        $base = $month
            ? Carbon::createFromFormat('Y-m', $month)->startOfMonth()
            : now()->startOfMonth();

        $start = $base->copy()->startOfMonth();
        $end   = $base->copy()->endOfMonth();

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $id)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $attendanceIds = $attendances->pluck('id')->filter()->all();

        $latestAppsByAttendanceId = Application::whereIn('attendance_id', $attendanceIds)
            ->orderBy('attendance_id')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('attendance_id')
            ->map(fn ($g) => $g->first());

        $attendancesByDate = $attendances->keyBy(fn ($a) => $a->attendance_date->toDateString());

        $attendancesForView = collect(CarbonPeriod::create($start, $end))
            ->map(function (Carbon $date) use ($attendancesByDate, $latestAppsByAttendanceId) {

                $attendance = $attendancesByDate->get($date->toDateString());
                $latestApp  = $attendance ? $latestAppsByAttendanceId->get($attendance->id) : null;

                return [
                    'firstCol'    => $date->isoFormat('MM/DD(ddd)'),
                    'startLabel'  => $attendance?->start_label ?? '',
                    'finishLabel' => $attendance?->finish_label ?? '',
                    'breakLabel'  => $attendance?->break_label ?? '',
                    'workLabel'   => $attendance?->work_label ?? '',
                    'detailUrl' => (
                        $latestApp instanceof \App\Models\Application
                        && $latestApp->application_status_id === 1
                    )
                        ? url('/attendance/pending/'.$latestApp->id.'?date='.$date->toDateString())
                        : (
                            $attendance?->id
                                ? url('/admin/attendance/'.$attendance->id.'?date='.$date->toDateString())
                                : url('/admin/attendance?date='.$date->toDateString())
                        ),
                ];
            });

        return view('admin.admin-attendance-list', [
            'attendances'    => $attendancesForView,
            'currentMonth'   => $base,
            'targetUser'     => $targetUser,
        ]);
    }

    // 全ユーザーの勤怠一覧表示
    public function attendanceListAllUsers(Request $request){
        $day = $request->input('day');

        $targetday = $day
            ? Carbon::createFromFormat('Y-m-d', $day)->startOfDay()
            : now()->startOfDay();

        $attendances = Attendance::with(['user', 'breakTimes'])
            ->whereDate('attendance_date', $targetday->toDateString())
            ->whereNotNull('attendance_start_at')
            ->orderBy('user_id')
            ->get();

        $attendanceIds = $attendances->pluck('id')->all();

        $latestAppsByAttendanceId = Application::whereIn('attendance_id', $attendanceIds)
            ->orderBy('attendance_id')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('attendance_id')
            ->map(fn ($g) => $g->first());

        $attendances = $attendances->map(function (Attendance $attendance) use ($latestAppsByAttendanceId) {

        $latestApp = $latestAppsByAttendanceId->get($attendance->id);

        return [
            'firstCol'    => $attendance->user?->name ?? '',
            'startLabel'  => $attendance->start_label,
            'finishLabel' => $attendance->finish_label,
            'breakLabel'  => $attendance->break_label,
            'workLabel'   => $attendance->work_label,

            'detailUrl' => (
                $latestApp && $latestApp->application_status_id === 1
            )
                ? url('/attendance/pending/' . $latestApp->id . '?date=' . $attendance->attendance_date->toDateString())
                : url('/admin/attendance/' . $attendance->id . '?date=' . $attendance->attendance_date->toDateString()),
        ];
    });

        return view('admin.attendance-list-all-users', [
            'attendances' => $attendances,
            'date' => $targetday,
        ]);
    }

    // 勤怠詳細
    public function adminAttendanceDetail($attendance_id){
        $attendance = Attendance::with('user', 'breakTimes')
            ->findOrFail($attendance_id);

        // 休憩データ数＋空1行を取得
        $breakRows = $attendance->breakTimes
            ->map(function ($break) {
                return [
                    'id'     => $break->id,
                    'start'  => $break->break_time_start_at ? substr($break->break_time_start_at, 0, 5) : '',
                    'finish' => $break->break_time_finish_at ? substr($break->break_time_finish_at, 0, 5) : '',
                ];
            })
            ->values();

        // 追加用の空行
        $breakRows->push([
            'id' => '',
            'start' => '',
            'finish'=> '',
        ]);

        return view('admin.admin-attendance-detail', compact('attendance', 'breakRows'));
    }

    // CSV出力
    public function export(Request $request, int $id): StreamedResponse
    {
        $targetUser = User::findOrFail($id);

        $month = $request->query('month');
        $base = $month ? Carbon::createFromFormat('Y-m', $month)->startOfMonth() : now()->startOfMonth();
        $start = $base->copy()->startOfMonth();
        $end   = $base->copy()->endOfMonth();

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $id)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn ($a) => $a->attendance_date->toDateString());

        $filename = $targetUser->name . '_attendance_' . $base->format('Ym') . '.csv';

        return response()->streamDownload(function () use ($attendances, $start, $end) {
            $out = fopen('php://output', 'w');

            // ヘッダ行
            $header = ['日付', '出勤', '退勤', '休憩', '合計'];
            fputcsv($out, array_map(fn($v) => mb_convert_encoding($v, 'SJIS-win', 'UTF-8'), $header));

            foreach (CarbonPeriod::create($start, $end) as $date) {
                $a = $attendances->get($date->toDateString());

                $row = [
                    $date->isoFormat('MM/DD(ddd)'),
                    $a?->attendance_start_at ? $a->attendance_start_at->format('H:i') : '',
                    $a?->attendance_finish_at ? $a->attendance_finish_at->format('H:i') : '',
                    $a?->break_label ?? '',  // 休憩（H:MM）
                    $a?->work_label  ?? '',  // 合計（H:MM）
                ];

                fputcsv($out, array_map(fn($v) => mb_convert_encoding((string)$v, 'SJIS-win', 'UTF-8'), $row));
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=SJIS-win',
        ]);

    }
}
