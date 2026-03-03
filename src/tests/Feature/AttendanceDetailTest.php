<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AttendanceDetailTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void{
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    // 勤怠詳細画面の「名前」がログインユーザーの氏名になっている
    // 勤怠詳細画面の「日付」が選択した日付になっている
    // 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
    // 「休憩」にて記されている時間がログインユーザーの打刻と一致している
    public function test_transition_to_attendance_detail_from_the_list(){
        $loginUser = User::factory()->create();

        // テストデータ作成
        $attendance = \App\Models\Attendance::create([
            'user_id' => $loginUser->id,
            'attendance_date' => Carbon::parse('2026-02-26'),
            'attendance_start_at' => '09:00',
            'attendance_finish_at' => '18:00',
            'attendance_status_id' => 4,
        ]);

        $breaktime = \App\Models\BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_time_date' => Carbon::parse('2026-02-26'),
            'break_time_start_at' => '12:00',
            'break_time_finish_at' => '13:00',
        ]);

        // 詳細を表示
        $response = $this->actingAs($loginUser)->get('/attendance/detail/' . $attendance->id);
        $response->assertStatus(200);

        // 詳細画面に勤怠情報が表示される
        $response->assertSee($loginUser->name); // 名前
        $response->assertSee('2026年');         // 年
        $response->assertSee('2月26日');        // 日付
        $response->assertSee('09:00');          // 出勤
        $response->assertSee('18:00');          // 退勤
        $response->assertSee('12:00');          // 休憩開始
        $response->assertSee('13:00');          // 休憩終了
    }
}
