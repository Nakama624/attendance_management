<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // 管理者
    public function isManager(){
        return $this->manager_flg == 1;
    }

    // リレーション
    // 申請者ユーザー
    public function applicationsUsers(){
        return $this->hasMany(Application::class, 'user_id');
    }

    // 承認者ユーザー
    public function approvalUsers(){
        return $this->hasMany(Application::class, 'manager_id');
    }

    public function attendances(){
        return $this->hasMany(Attendance::class);
    }
}
