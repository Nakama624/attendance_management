<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'attendance_id',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    // リレーション
    public function application(){
        return $this -> belongsTo(Application::class, 'application_id');
    }
}
