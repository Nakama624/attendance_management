<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AdminAttendanceListTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void{
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    // その日になされた全ユーザーの勤怠情報が正確に確認できる
    public function test_display_all_users_attendance_list(){
        Carbon::setTestNow('2026-02-26');

        // 一般ユー7
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        // 管理者
        $adminUser = User::factory()->create([
            'manager_flg' => 1,
        ]);

        // テストデータ1
        $attendance1 = \App\Models\Attendance::create([
            'user_id' => $user1->id,
            'attendance_date' => Carbon::parse('2026-02-26'),
            'attendance_start_at' => '09:00',
            'attendance_finish_at' => '18:00',
            'attendance_status_id' => 4,
        ]);

        $breaktime1 = \App\Models\BreakTime::create([
            'attendance_id' => $attendance1->id,
            'break_time_date' => Carbon::parse('2026-02-26'),
            'break_time_start_at' => '12:00',
            'break_time_finish_at' => '13:00',
        ]);

        // テストデータ2
        $attendance2 = \App\Models\Attendance::create([
            'user_id' => $user2->id,
            'attendance_date' => Carbon::parse('2026-02-26'),
            'attendance_start_at' => '10:00',
            'attendance_finish_at' => '',
            'attendance_status_id' => 3,
        ]);

        $breaktime2 = \App\Models\BreakTime::create([
            'attendance_id' => $attendance2->id,
            'break_time_date' => Carbon::parse('2026-02-26'),
            'break_time_start_at' => '13:00',
            'break_time_finish_at' => '',
        ]);

        // テストデータ3（休憩2回））
        $attendance3 = \App\Models\Attendance::create([
            'user_id' => $user3->id,
            'attendance_date' => Carbon::parse('2026-02-26'),
            'attendance_start_at' => '12:00',
            'attendance_finish_at' => '',
            'attendance_status_id' => 2,
        ]);

        $breaktime3 = \App\Models\BreakTime::create([
            'attendance_id' => $attendance3->id,
            'break_time_date' => Carbon::parse('2026-02-26'),
            'break_time_start_at' => '15:00',
            'break_time_finish_at' => '16:00',
        ]);

        $breaktime4 = \App\Models\BreakTime::create([
            'attendance_id' => $attendance3->id,
            'break_time_date' => Carbon::parse('2026-02-26'),
            'break_time_start_at' => '17:00',
            'break_time_finish_at' => '17:30',
        ]);


        $response = $this->actingAs($adminUser)->get('/admin/attendance/list');
        $response->assertStatus(200);

        $response->assertSeeInOrder([
            $user1->name,
            '09:00',
            '18:00',
            '1:00',
            '8:00',
        ]);
        $response->assertSeeInOrder([
            $user2->name,
            '10:00',
            '',
            '',
            '',
        ]);
        $response->assertSeeInOrder([
            $user3->name,
            '12:00',
            '',
            '1:30',
            '',
        ]);
    }
    // 遷移した際に現在の日付が表示される
    public function test_display_current_date_on_the_attendance_list(){
        Carbon::setTestNow('2026-02-26');

        $adminUser = User::factory()->create([
            'manager_flg' => 1,
        ]);

        $response = $this->actingAs($adminUser)->get('/admin/attendance/list');
        $response->assertStatus(200);

        $date = Carbon::now()->format('Y/m/d');
        $response->assertSee($date);
    }

    // 「前日」を押下した時に前の日の勤怠情報が表示される
    public function test_display_last_date_on_the_attendance_list(){
        Carbon::setTestNow('2026-02-26');

        $adminUser = User::factory()->create([
            'manager_flg' => 1,
        ]);

        $response = $this->actingAs($adminUser)->get('/admin/attendance/list');
        $response->assertStatus(200);

        // 前日を押す
        $response = $this->actingAs($adminUser)
            ->get('/admin/attendance/list?day=2026-02-25');

        $response->assertStatus(200);
        $response->assertSee('2026/02/25');
    }

    // 「翌日」を押下した時に次の日の勤怠情報が表示される
    public function test_display_next_date_on_the_attendance_list(){
        Carbon::setTestNow('2026-02-26');

        $adminUser = User::factory()->create([
            'manager_flg' => 1,
        ]);

        $response = $this->actingAs($adminUser)->get('/admin/attendance/list');
        $response->assertStatus(200);

        // 前日を押す
        $response = $this->actingAs($adminUser)
            ->get('/admin/attendance/list?day=2026-02-27');

        $response->assertStatus(200);
        $response->assertSee('2026/02/27');
    }
}
