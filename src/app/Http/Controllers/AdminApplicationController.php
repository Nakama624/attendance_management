<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\TimeRequest;
use App\Models\Application;
use App\Models\Attendance;
use App\Models\PendingApplication;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminApplicationController extends Controller
{
    // 申請詳細を表示
    // 申請ステータス1:承認待ちの場合は承認ボタンを表示
    // 申請ステータス2:承認済みの場合は承認済みを表示
    public function applicationDetail($application_id){
        $application = Application::with('user', 'attendance')->findOrFail($application_id);

        $isPending = ($application->application_status_id === 1);

        if ($isPending) {
            // 承認待ち：PendingApplication の changes を表示
            $pending = PendingApplication::where('application_id', $application->id)
                ->latest()
                ->first();

            $changes = $pending?->changes ?? [];

            $userName = $application->user->name;
            $date     = $application->attendance->attendance_date ?? null;

            $start    = substr($changes['attendance_start_at'] ?? '', 0, 5);
            $finish   = substr($changes['attendance_finish_at'] ?? '', 0, 5);

            $breakRows = collect($changes['breaks'] ?? [])
                ->map(fn($row) => [
                    'start'  => $row['start'] ?? '',
                    'finish' => $row['finish'] ?? '',
                ])->values()->all();

            $remarks = $changes['remarks'] ?? '';

        } else {
            // 承認済み：Attendance（確定データ）を表示
            $attendance = Attendance::with('user', 'breakTimes')
                ->findOrFail($application->attendance_id); // ← $attendance_id は未定義なのでここを修正

            $userName = $attendance->user->name;
            $date     = $attendance->attendance_date;

            $start  = $attendance->attendance_start_at
                ? \Carbon\Carbon::parse($attendance->attendance_start_at)->format('H:i')
                : '';

            $finish = $attendance->attendance_finish_at
                ? \Carbon\Carbon::parse($attendance->attendance_finish_at)->format('H:i')
                : '';

            $breakRows = $attendance->breakTimes->map(fn($break) => [
                'start'  => $break->break_time_start_at
                    ? \Carbon\Carbon::parse($break->break_time_start_at)->format('H:i')
                    : '',
                'finish' => $break->break_time_finish_at
                    ? \Carbon\Carbon::parse($break->break_time_finish_at)->format('H:i')
                    : '',
            ])->values()->all();

            $remarks = $attendance->remarks ?? '';
        }
        return view('admin.display-application-detail', compact(
            'application',
            'application_id',
            'isPending',
            'userName',
            'date',
            'start',
            'finish',
            'breakRows',
            'remarks'
        ));
    }

    // 承認する
    public function approved(Request $request, $application_id){
        $application = Application::findOrFail($application_id);

        // pending の変更内容を取得
        $pending = PendingApplication::where('application_id', $application->id)
            ->latest()
            ->firstOrFail();

        $changes = $pending->changes ?? [];

        // Application 更新
        $application->update([
            'approver_id' => Auth::id(),
            'application_status_id' => 2,
            'approval_at' => now(),
        ]);

        // Attendance 更新
        $attendance = Attendance::with('breakTimes')->findOrFail($application->attendance_id);

        $start  = $changes['attendance_start_at'] ?? null;
        $finish = $changes['attendance_finish_at'] ?? null;

        $attendance->update([
            'attendance_start_at'  => $start ? Carbon::parse($attendance->attendance_date->toDateString().' '.$start) : $attendance->attendance_start_at,
            'attendance_finish_at' => $finish ? Carbon::parse($attendance->attendance_date->toDateString().' '.$finish) : $attendance->attendance_finish_at,
            'remarks'              => $changes['remarks'] ?? $attendance->remarks,
        ]);

        $attendance->breakTimes()->delete();

        foreach (($changes['breaks'] ?? []) as $row) {
            $s = $row['start'] ?? null;
            $f = $row['finish'] ?? null;

            // 空行は作らない
            if (($s ?? '') === '' && ($f ?? '') === '') continue;

            $attendance->breakTimes()->create([
                'break_time_date'      => $attendance->attendance_date,
                'break_time_start_at'  => $s ?: null,
                'break_time_finish_at' => $f ?: null,
            ]);
        }

        $attendance->load('breakTimes');

        $attendance->update([
            'attendance_status_id' => $attendance->computeStatusId(),
        ]);

        return redirect('/stamp_correction_request/approve/' . $application->id);
    }

    // 管理者の場合は、勤怠の修正申請は不要とし直接更新
    public function adminAttendanceModify(TimeRequest $request, $attendance_id){
        return DB::transaction(function () use ($request, $attendance_id) {

            $attendance = Attendance::with('breakTimes')->findOrFail($attendance_id);

            // 勤怠本体を更新
            $attendance->update([
                'attendance_start_at'  => $request->attendance_start_at,
                'attendance_finish_at' => $request->attendance_finish_at,
                'remarks'              => $request->remarks,
            ]);

            // 休憩を更新（全削除→作成）
            $attendance->breakTimes()->delete();

            foreach (($request->breaks ?? []) as $row) {
                $start  = $row['start']  ?? null;
                $finish = $row['finish'] ?? null;

                // 両方空の行はスキップ（空行対策）
                if (!$start && !$finish) {
                    continue;
                }

                $attendance->breakTimes()->create([
                    'break_time_date'      => $attendance->attendance_date,
                    'break_time_start_at'  => $start,
                    'break_time_finish_at' => $finish,
                ]);
            }

            // ステータスを取得
            $attendance->load('breakTimes');
            $attendance->update([
                'attendance_status_id' => $attendance->computeStatusId(),
            ]);

            return redirect('/admin/attendance/' . $attendance->id)
                ->with('success', '修正しました');
        });
    }
}
