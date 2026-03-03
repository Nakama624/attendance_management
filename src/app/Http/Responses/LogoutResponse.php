<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Support\Facades\Auth;

class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request)
    {
        $user = Auth::user();

        // すでに logout 済みの可能性があるので session から取得してもOK
        // ただし通常は logout 前に呼ばれるので user は取れる

        if ($user && $user->isManager()) {
            return redirect('/admin/login');
        }

        return redirect('/login');
    }
}
