<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\LogoutResponse;
use App\Http\Responses\LogoutResponse as CustomLogoutResponse;

// 静的解析ツールで必要
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Fortify：LoginRequest→Request/LoginRequest に変更する
        $this->app->bind(
            \Laravel\Fortify\Http\Requests\LoginRequest::class,
            \App\Http\Requests\LoginRequest::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        // Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        // Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        // Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // RateLimiter::for('login', function (Request $request) {
        //     $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

        //     return Limit::perMinute(5)->by($throttleKey);
        // });

        // RateLimiter::for('two-factor', function (Request $request) {
        //     return Limit::perMinute(5)->by($request->session()->get('login.id'));
        // });
        Fortify::registerView(function () {
            return view('register');
        });

        Fortify::loginView(function (Request $request) {
            $request->session()->put('is_admin_login', $request->boolean('is_admin_login'));

            if (! $request->boolean('is_admin_login')) {
                $request->session()->put('url.intended', '/attendance');
            }

            return view('staff.login');
        });

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(10)->by($email . $request->ip());
        });

        // メール認証関連（詳細不明）
        $this->app->singleton(\Laravel\Fortify\Contracts\RegisterResponse::class, \App\Http\Responses\RegisterResponse::class);
        $this->app->singleton(\Laravel\Fortify\Contracts\LoginResponse::class, \App\Http\Responses\LoginResponse::class);

        // ログイン後メール未認証で画面を遷移しようとした場合、メール認証案内画面に遷移する
        Fortify::verifyEmailView(function () {
            return view('mail');
        });

        // ログアウト後はログイン画面に戻る
        $this->app->singleton(LogoutResponse::class, function () {
            return new class implements LogoutResponse {
                public function toResponse($request)
                {
                    // ★フォームから送る hidden を見る（セッションではない）
                    $isAdminLogout = $request->boolean('is_admin_logout');

                    return $isAdminLogout
                        ? redirect('/admin/login')
                        : redirect('/login');
                }
            };
        });

        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return null;
            }

            // 管理者ログインの場合のみ manager_flg=1 を必須にする
            if ($request->boolean('is_admin_login') && (int)$user->manager_flg !== 1) { // ★修正
                return null; // 管理者でないのに管理者ログインしようとした
            }

            return $user;
        });
    }
}
