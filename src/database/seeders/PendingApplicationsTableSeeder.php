<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PendingApplication;


class PendingApplicationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'application_id' => '1',
            'attendance_id' => '5',
            'changes' => [
                'attendance_start_at' => '10:00',
                'attendance_finish_at' => '19:00',
                'breaks' => [
                    [
                        'start'  => '12:00',
                        'finish' => '13:00',
                    ]
                ],
                'remarks' => '休憩を追加で入力',
            ]
        ];
        PendingApplication::create($param);

        $param = [
            'application_id' => '2',
            'attendance_id' => '6',
            'changes' => [
                'attendance_start_at' => '9:30',
                'attendance_finish_at' => '16:30',
                'breaks' => [
                    [
                        'start' => '',
                        'finish' => '',
                    ]
                ],
                'remarks' => '退勤時間を修正',
            ]
        ];
        PendingApplication::create($param);
    }
}
