<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampingController;
use App\Http\Controllers\ApplicationController;

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
        Route::get('/attendance', [StampingController::class, 'stamping']);
        Route::post('/attendance/start', [StampingController::class, 'startAt']); // 出勤
        Route::post('/attendance/finish', [StampingController::class, 'finishAt']); // 退勤
        Route::post('/attendance/start_break_time', [StampingController::class, 'startBreakTimeAt']); // 休憩開始
        Route::post('/attendance/finish_break_time', [StampingController::class, 'finishBreakTimeAt']); // 休憩終了


        // 勤怠一覧画面（一般ユーザー）
        Route::get('/attendance/list', [AttendanceController::class, 'attendanceList'])
            ->name('attendance.list');

        // 勤怠詳細画面（一般ユーザー）
        Route::get('/attendance/detail/{attendance_id}', [AttendanceController::class, 'attendanceDetail']);


        // 申請一覧画面（一般ユーザー）
        Route::get('/stamp_correction_request/list', [ApplicationController::class, 'requestList']);
    });

    Route::middleware('verified', 'admin')->group(function () {
        // =================
        // 勤怠一覧画面（管理者）
        Route::get('/admin/attendance/list', function () {
            return view('admin.admin-list');
        });

        // 勤怠詳細画面（管理者）
        Route::get('/admin/attendance/1', function () {
            return view('');
        });

        // スタッフ別勤怠一覧画面（管理者）
        Route::get('/admin/attendance/staff/1', function () {
            return view('');
        });

        // 申請一覧画面（管理者）
        // Route::get('/stamp_correction_request/list', function () {
        //     return view('');
        // });

        // 修正申請承認画面（管理者）
        Route::get('/stamp_correction_request/approve/1', function () {
            return view('');
        });
    });
});









