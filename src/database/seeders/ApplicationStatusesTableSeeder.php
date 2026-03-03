<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ApplicationStatus;

class ApplicationStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){
        $param = [
            'status_name' => '承認待ち',
        ];
        ApplicationStatus::create($param);

        $param = [
            'status_name' => '承認済み',
        ];
        ApplicationStatus::create($param);
    }
}
