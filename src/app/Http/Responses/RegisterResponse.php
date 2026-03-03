<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
  public function toResponse($request)
  {
    // メール認証案内画面へ
    return redirect('/mail');
  }
}
