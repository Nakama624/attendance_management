<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampingController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminApplicationController;
use App\Http\Controllers\StaffController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


// ログイン画面（管理者）
Route::get('/admin/login', fn()=> view('admin.admin-login'))->middleware('guest');

// 一般スタッフ
// 認証
Route::middleware('auth')->group(function () {
    //＝＝＝メール認証＝＝＝
    Route::get('/mail', function () {
        $user = request()->user();

        if ($user->hasVerifiedEmail()) {
            return redirect('/attendance');
        }

        return view('mail');
    });

    // 「認証はこちら」からMailHog を開く（開発時を想定しdev）
    Route::get('/dev/mailhog/open', function () {
        abort_unless(app()->environment('local'), 404);

        // MailHog を開く
        return redirect()->away('http://localhost:8025');
    });

    // 認証メール再送
    Route::post('/email/verification-notification', function () {
        request()->user()->sendEmailVerificationNotification();
        return back()->with('status', 'verification-link-sent');
    });

    Route::middleware('verified')->group(function () {

        // 出勤登録画面（一般ユーザー）
        Route::get('/attendance', [StampingController::class, 'stamping'])->name('attendance.stamping');
        Route::post('/attendance/start', [StampingController::class, 'startAt']); // 出勤
        Route::patch('/attendance/finish', [StampingController::class, 'finishAt']); // 退勤
        Route::post('/attendance/start_break_time', [StampingController::class, 'startBreakTimeAt']); // 休憩開始
        Route::patch('/attendance/finish_break_time', [StampingController::class, 'finishBreakTimeAt']); // 休憩終了


        // 勤怠一覧画面（一般ユーザー）
        Route::get('/attendance/list', [AttendanceController::class, 'attendanceList'])
            ->name('attendance.list');

        // 勤怠詳細画面（一般ユーザー）
        Route::get('/attendance/detail/{attendance_id?}', [AttendanceController::class, 'attendanceDetail']);
        Route::post('/attendance/modify/{attendance_id?}', [ApplicationController::class, 'attendanceModify'])
            ->name('attendance.modify');

        // 申請一覧画面（一般ユーザー）
        Route::get('/stamp_correction_request/list', [ApplicationController::class, 'applicationList']);
        // 申請→勤怠詳細(承認待ち)
        Route::get('/attendance/pending/{application_id}', [ApplicationController::class, 'applicationDetail']);
    });

    Route::middleware('verified', 'admin')->group(function () {
        // =================
        // 勤怠一覧画面（管理者）
        Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'attendanceListAllUsers'])
            ->name('admin.attendance.list');

        // 勤怠詳細画面（管理者）
        Route::get('/admin/attendance/{attendance_id}', [AdminAttendanceController::class, 'adminAttendanceDetail']);
        Route::post('/admin/attendance/modify/{attendance_id}', [AdminApplicationController::class, 'adminAttendanceModify']);

        // スタッフ一覧
        Route::get('/admin/staff/list', [StaffController::class, 'staffList']);

        // スタッフ別勤怠一覧画面（管理者）
        Route::get('/admin/attendance/staff/{id}', [AdminAttendanceController::class, 'adminAttendanceList'])
            ->name('admin.attendance.staff');

        // CSV出力
        Route::get('/admin/attendance/staff/{id}/export', [AdminAttendanceController::class, 'export'])
            ->name('admin.attendance.staff.export');

        // 修正申請承認画面（管理者）
        Route::get('/stamp_correction_request/approve/{application_id}', [AdminApplicationController::class, 'applicationDetail']);
        Route::patch('/stamp_correction_request/approve/{application_id}', [AdminApplicationController::class, 'approved'])
            ->name('stamp_correction_request.approved');
    });
});









