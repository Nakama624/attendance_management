<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'user_id' => '2',
            'attendance_date' => Carbon::today()->format('Y-m-d'),
            'attendance_start_at' => '10:30:56',
            'attendance_finish_at' => '19:30:56',
            'attendance_status_id' => '4',
            'remarks' => '勤怠備考1',
        ];
        Attendance::create($param);

        $param = [
            'user_id' => '1',
            'attendance_date' => Carbon::today()->format('Y-m-d'),
            'attendance_start_at' => '10:30:56',
            'attendance_finish_at' => null,
            'attendance_status_id' => '2',
            'remarks' => '勤怠備考2',
        ];
        Attendance::create($param);

        $param = [
            'user_id' => '3',
            'attendance_date' => Carbon::today()->format('Y-m-d'),
            'attendance_start_at' => '10:30:56',
            'attendance_finish_at' => null,
            'attendance_status_id' => '3',
            'remarks' => '勤怠備考3',
        ];
        Attendance::create($param);

        $param = [
            'user_id' => '1',
            'attendance_date' => '2026-02-23',
            'attendance_start_at' => '09:00:00',
            'attendance_finish_at' => '18:00:00',
            'attendance_status_id' => 4,
        ];
        Attendance::create($param);

        $param = [
            'user_id' => '1',
            'attendance_date' => '2026-02-24',
            'attendance_start_at' => '9:30',
            'attendance_finish_at' => '16:30',
            'attendance_status_id' => 4,
            'remarks' => '休憩を追加で入力',
        ];
        Attendance::create($param);

        $param = [
            'user_id' => '1',
            'attendance_date' => '2026-02-25',
            'attendance_start_at' => '09:30:00',
            'attendance_finish_at' => '18:30:00',
            'attendance_status_id' => 4,

        ];
        Attendance::create($param);
    }
}
