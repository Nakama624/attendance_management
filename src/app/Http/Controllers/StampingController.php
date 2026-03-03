<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class StampingController extends Controller{
    // 勤怠登録画面を表示
    public function stamping(){
        $attendance = Attendance::with('attendanceStatus')
            ->where('user_id', Auth::id())
            ->where('attendance_date', today())
            ->first();

        // ステータスを仮作成
        if (!$attendance) {
            $attendance = new Attendance([
                'attendance_status_id' => 1
            ]);
        }
        return view('staff.attendance-stamp', compact('attendance'));
    }

    // 出勤
    public function startAt(Request $request){

        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('attendance_date', Carbon::today())
            ->first();

        if($attendance){
            return redirect('/attendance');
        }

        $attendance = Attendance::create([
            'user_id' => Auth::id(),
            'attendance_date' => Carbon::today(),
            'attendance_start_at' => Carbon::now(),
            'attendance_status_id' => 2,
        ]);

        return redirect()->back();
    }

    // 退勤
    public function finishAt(Request $request){
        // ログインユーザーかつ今日の勤怠カラムを更新
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('attendance_date', Carbon::today())
            ->first();

        if ($attendance) {
            $attendance->update([
                'attendance_finish_at' => Carbon::now(),
                'attendance_status_id' => 4, // 退勤済
            ]);
        }
        return redirect()->back();
    }

    // 休憩開始
    public function startBreakTimeAt(Request $request){

        $startBreakTime = Attendance::where('user_id', auth()->id())
            ->whereDate('attendance_date', Carbon::today())
            ->firstOrFail();

        // 勤怠ステータスを更新する
        $startBreakTime->update([
            'attendance_status_id' => 3, // 休憩中
        ]);

        // 休憩カラムを作成する
        $startBreakTime->breakTimes()->create([
            'break_time_date' => Carbon::today(),
            'break_time_start_at' => Carbon::now(),
        ]);

        return redirect()->back();
    }

    // 休憩戻る
    public function finishBreakTimeAt(Request $request){
        $attendance = Attendance::where('user_id', Auth::id())
            ->whereDate('attendance_date', today())
            ->firstOrFail();

        $attendance->update([
            'attendance_status_id' => 2, // 休憩中
        ]);

        // ログインユーザーかつ今日の勤怠カラムを更新
        $finishBreakTime = $attendance->breakTimes()
            ->whereNull('break_time_finish_at')
            ->latest('break_time_start_at')
            ->first();

        if ($finishBreakTime) {
            $finishBreakTime->update([
                'break_time_finish_at' => now(),
            ]);
        }
        return redirect()->back();
    }
}
