<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'break_time_date',
        'break_time_start_at',
        'break_time_finish_at',
    ];

    // リレーション
    public function attendance(){
        return $this -> belongsTo(Attendance::class);
    }
}
