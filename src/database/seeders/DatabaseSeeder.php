<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $this->call(UsersTableSeeder::class);
        $this->call(ApplicationStatusesTableSeeder::class);
        $this->call(AttendanceStatusesTableSeeder::class);
        $this->call(AttendancesTableSeeder::class);
        $this->call(ApplicationsTableSeeder::class);
        $this->call(BreakTimesTableSeeder::class);
        $this->call(PendingApplicationsTableSeeder::class);
    }
}
