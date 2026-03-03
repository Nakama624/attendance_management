<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        // 未認証ならメール誘導画面へ（管理者＆一般）
        if ($user && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
            return redirect('/mail');
        }

        // 管理者
        if ($request->boolean('is_admin_login')) {
            return redirect('/admin/attendance/list');
        }

        // 一般
        return redirect('/attendance');
    }
}
