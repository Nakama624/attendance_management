<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BreakTime;

class BreakTimesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'attendance_id' => '2',
            'break_time_date' => '2026-01-28',
            'break_time_start_at' => '12:30:00',
            'break_time_finish_at' => null,
        ];
        BreakTime::create($param);

        $param = [
            'attendance_id' => '1',
            'break_time_date' => '2025-04-02',
            'break_time_start_at' => '13:15:00',
            'break_time_finish_at' => '15:00:32',
        ];
        BreakTime::create($param);

        $param = [
            'attendance_id' => '5',
            'break_time_date' => '2025-04-02',
            'break_time_start_at' => '12:00:00',
            'break_time_finish_at' => '13:00:00',
        ];
        BreakTime::create($param);

    }
}
