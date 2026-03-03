<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AttendanceListTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void{
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    // 自分が行った勤怠情報が全て表示されている
    public function test_display_attendance_list(){
        Carbon::setTestNow('2026-02-26');

        $loginUser = User::find(1);

        $response = $this->actingAs($loginUser)->get('/attendance/list');
        $response->assertStatus(200);

        $response->assertSeeInOrder([
            '02/23(月)',
            '09:00',
            '18:00',
        ]);
        $response->assertSeeInOrder([
            '02/24(火)',
            '9:30',
            '16:30',
        ]);
        $response->assertSeeInOrder([
            '02/25(水)',
            '09:30',
            '18:30',
        ]);
    }

    // 勤怠一覧画面に遷移した際に現在の月が表示される
    public function test_display_current_month_on_the_attendance_list(){
        Carbon::setTestNow('2026-02-26');

        $loginUser = User::find(1);

        $response = $this->actingAs($loginUser)->get('/attendance/list');
        $response->assertStatus(200);

        $month = Carbon::now()->format('Y/m');
        $response->assertSee($month);
    }

    // 「前月」を押下した時に表示月の前月の情報が表示される
    public function test_display_last_month_on_the_attendance_list(){
        Carbon::setTestNow('2026-02-26');

        $loginUser = User::find(1);

        $response = $this->actingAs($loginUser)->get('/attendance/list');
        $response->assertStatus(200);

        // 前月を押す
        $response = $this->actingAs($loginUser)
            ->get('/attendance/list?id=1&month=2026-01');

        $response->assertStatus(200);
        $response->assertSee('2026/01');
    }

    // 「翌月」を押下した時に表示月の前月の情報が表示される
    public function test_display_next_month_on_the_attendance_list(){
        Carbon::setTestNow('2026-02-26');

        $loginUser = User::find(1);

        $response = $this->actingAs($loginUser)->get('/attendance/list');
        $response->assertStatus(200);

        // 翌月を押す
        $response = $this->actingAs($loginUser)
            ->get('/attendance/list?id=1&month=2026-03');

        $response->assertStatus(200);
        $response->assertSee('2026/03');
    }

    // 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
    public function test_transition_to_attendance_detail_from_the_list(){
        $loginUser = User::factory()->create();

        // テストデータ作成
        $attendance = \App\Models\Attendance::create([
            'user_id' => $loginUser->id,
            'attendance_date' => Carbon::parse('2026-02-26'),
            'attendance_start_at' => Carbon::parse('09:00:00'),
            'attendance_finish_at' => Carbon::parse('18:00:00'),
            'attendance_status_id' => 4,
        ]);

        // 一覧を表示
        $response = $this->actingAs($loginUser)->get('/attendance/list');
        $response->assertStatus(200);

        // 詳細に遷移
        $response = $this->actingAs($loginUser)->get('/attendance/detail/' . $attendance->id);
        $response->assertStatus(200);

        // 詳細画面に勤怠情報が表示される
        $response->assertSee('2026年');
        $response->assertSee('2月26日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}
