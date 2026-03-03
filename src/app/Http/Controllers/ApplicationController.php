<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\TimeRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Application;
use App\Models\Attendance;
use App\Models\PendingApplication;

class ApplicationController extends Controller
{
    // 勤怠を修正して申請データを作成する
    public function attendanceModify(TimeRequest $request, $attendance_id = null){
        DB::beginTransaction();

        try {
            // attendance が無ければ作成、あれば取得
            if (empty($attendance_id)) {
                $attendance = Attendance::create([
                    'user_id'               => Auth::id(),
                    'attendance_date'       => $request->attendance_date,
                    'attendance_start_at'   => $request->attendance_start_at,
                    'attendance_finish_at'  => $request->attendance_finish_at,
                    'attendance_status_id'  => 1,
                    'remarks'               => $request->remarks ?? '',
                ]);

                $attendance_id = $attendance->id;
            } else {
                $attendance = Attendance::with('user', 'breakTimes')->findOrFail($attendance_id);
            }

            // 申請テーブル作成（承認待ち）
            $application = Application::create([
                'user_id'               => Auth::id(),
                'attendance_id'         => $attendance_id,
                'application_status_id' => 1,
            ]);

            // changes にまとめて保存（勤怠＋休憩＋備考）
            $changes = [
                'attendance_start_at'  => $request->attendance_start_at,
                'attendance_finish_at' => $request->attendance_finish_at,
                'breaks'               => collect($request->breaks ?? [])
                    ->map(fn ($b) => [
                        'start'  => $b['start'] ?? '',
                        'finish' => $b['finish'] ?? '',
                    ])->values()->all(),
                'remarks'              => $request->remarks,
            ];

            PendingApplication::create([
                'application_id' => $application->id,
                'attendance_id'      => $attendance_id,
                'changes'        => $changes,
            ]);

            DB::commit();

            // 表示用に user/breakTimes を必ずロード
            $attendance = Attendance::with('user', 'breakTimes')->findOrFail($attendance_id);

            // 表示用に変数を組み立てる
            $userName = $attendance->user->name ?? Auth::user()->name ?? '';

            $date = $request->attendance_date
                ? \Carbon\Carbon::parse($request->attendance_date)
                : ($attendance->attendance_date ? \Carbon\Carbon::parse($attendance->attendance_date) : null);

            $start   = $changes['attendance_start_at'];
            $finish  = $changes['attendance_finish_at'];
            $breakRows = $changes['breaks'];
            $remarks = $changes['remarks'] ?? '';

            return view('staff.display-application-detail', compact(
                'attendance',
                'changes',
                'application',
                'userName',
                'date',
                'start',
                'finish',
                'breakRows',
                'remarks'
            ));

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // 申請詳細を表示
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
                ->findOrFail($application->attendance_id);

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
        return view('staff.display-application-detail', compact(
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

    // 申請一覧の表示(一般ユーザー＆管理者共通)
    public function applicationList(Request $request){
        $user = Auth::user();
        $isManager = $user->isManager();

        $query = Application::with(['user','attendance', 'applicationStatus', 'latestPending']);

        // 一般ユーザーなら自分の分だけに絞る
        if (!$isManager) {
            $query->where('user_id', $user->id);
        }

        // 申請ステータスを見てタブ内の表示を変える
        if ($request->page == 'pending'){
            $query->where('application_status_id', 1);

        }elseif ($request->page == 'approved'){
            $query->where('application_status_id', 2);
        }

        // データ取得
        $applications = $query->get();

        return view('application-list', compact('applications', 'isManager'));
    }
}

