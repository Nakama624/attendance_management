<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Application;

class ApplicationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
            'user_id' => '1',
            'approver_id' => '4',
            'attendance_id' => '5',
            'application_status_id' => '2',
            'approval_at' => '2026-01-25 19:30:56',
        ];
        Application::create($param);

        $param = [
            'user_id' => '1',
            'approver_id' => '4',
            'attendance_id' => '6',
            'application_status_id' => '1',
            'approval_at' => null,
        ];
        Application::create($param);


    }
}
