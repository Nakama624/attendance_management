<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'approver_id',
        'attendance_id',
        'application_status_id',
        'approval_at',
    ];


    // リレーション
    // 申請者ユーザー
    public function user(){
        return $this->belongsTo(User::class, 'user_id'); // users.id を参照する
    }

    // 管理者ユーザー
    public function approver(){
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function applicationStatus(){
        return $this -> belongsTo(ApplicationStatus::class, 'application_status_id');
    }

    public function attendance(){
        return $this -> belongsTo(Attendance::class);
    }

    public function pendingApplications(){
        return $this -> hasMany(PendingApplication::class, 'application_id');
    }
    public function latestPending(){
        return $this->hasOne(PendingApplication::class)->latestOfMany();
    }

}
