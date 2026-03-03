<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_date',
        'attendance_start_at',
        'attendance_finish_at',
        'attendance_status_id',
        'remarks',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'attendance_start_at'  => 'datetime',
        'attendance_finish_at' => 'datetime',
    ];

    protected $appends = [
        'break_minutes',
        'work_minutes',
        'start_label',
        'finish_label',
        'break_label',
        'work_label',
    ];

    /* =======================
     * Relations
     * ======================= */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function attendanceStatus()
    {
        return $this->belongsTo(AttendanceStatus::class, 'attendance_status_id');
    }

    /* =======================
     * Accessors（勤務時間ロジック）
     * ======================= */

    // 休憩合計（分）
    public function getBreakMinutesAttribute(): int
    {
        if (!$this->relationLoaded('breakTimes')) {
            $this->load('breakTimes');
        }

        return (int) $this->breakTimes->sum(function ($b) {
            if (!$b->break_time_start_at || !$b->break_time_finish_at) return 0;

            return Carbon::parse($b->break_time_finish_at)
                ->diffInMinutes(Carbon::parse($b->break_time_start_at));
        });
    }

    // 勤務合計（分）= (退勤-出勤) - 休憩
    public function getWorkMinutesAttribute(): int
    {
        if (!$this->attendance_start_at || !$this->attendance_finish_at) {
            return 0;
        }

        $total = Carbon::parse($this->attendance_finish_at)
            ->diffInMinutes(Carbon::parse($this->attendance_start_at));

        return max(0, $total - $this->break_minutes);
    }

    // 出勤表示（H:i）
    public function getStartLabelAttribute(): string
    {
        return $this->attendance_start_at
            ? Carbon::parse($this->attendance_start_at)->format('H:i')
            : '';
    }

    // 退勤表示（H:i）
    public function getFinishLabelAttribute(): string
    {
        return $this->attendance_finish_at
            ? Carbon::parse($this->attendance_finish_at)->format('H:i')
            : '';
    }

    // 休憩表示（H:MM）
    public function getBreakLabelAttribute(): string
    {
        return $this->formatMinutes($this->break_minutes);
    }

    // 合計表示（H:MM）
    public function getWorkLabelAttribute(): string
    {
        return $this->formatMinutes($this->work_minutes);
    }

    private function formatMinutes(?int $m): string
    {
        if (!$m) return '';
        $h = intdiv($m, 60);
        $min = $m % 60;
        return $h . ':' . str_pad((string) $min, 2, '0', STR_PAD_LEFT);
    }

    public function computeStatusId(): int{
        // 退勤がある → 4
        if (!is_null($this->attendance_finish_at)) {
            return 4;
        }

        // 直近休憩が未終了 → 3
        $latestBreak = $this->breakTimes()
            ->orderByDesc('break_time_start_at')
            ->first();

        if ($latestBreak && is_null($latestBreak->break_time_finish_at)) {
            return 3;
        }

        // 出勤がある → 2
        if (!is_null($this->attendance_start_at)) {
            return 2;
        }

        // それ以外 → 1
        return 1;
    }
}
