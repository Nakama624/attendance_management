<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceStatus;

class AttendanceStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'status_name' => '勤務外',
        ];
        AttendanceStatus::create($param);

        $param = [
            'status_name' => '出勤中',
        ];
        AttendanceStatus::create($param);

        $param = [
            'status_name' => '休憩中',
        ];
        AttendanceStatus::create($param);

        $param = [
            'status_name' => '退勤済',
        ];
        AttendanceStatus::create($param);
    }
}
