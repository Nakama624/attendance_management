<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'status_name',
    ];

    // リレーション
    public function attendance(){
        return $this -> belongsTo(Attendance::class);
    }
}
