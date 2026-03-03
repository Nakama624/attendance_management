<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class StampingFinishWorkTest extends TestCase
{

    use DatabaseMigrations;

    protected function setUp(): void{
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    // 退勤ボタンが正しく機能する
    public function test_attendance__finish_button_works_properly(){

        $loginUser = User::factory()->create();

        // 出勤中の勤怠レコードを作る
        \App\Models\Attendance::create([
            'user_id' => $loginUser->id,
            'attendance_date' => Carbon::today(),
            'attendance_start_at' => Carbon::parse('09:00:00'),
            'attendance_status_id' => 2, // 出勤中
        ]);

        // ステータスが出勤中、退勤ボタンが表示されている
        $response = $this->actingAs($loginUser)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤中');
        $response->assertSee(
            '<button class="attendance__btn--black" type="submit">退勤</button>',
            false
        );

        // 退勤ボタン押下
        Carbon::setTestNow(
            today()->setTime(18, 0)
        );
        $response = $this->actingAs($loginUser)->patch('/attendance/finish');
        $response->assertStatus(302); // リダイレクト

        // DB確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $loginUser->id,
            'attendance_date' => today()->toDateString(),
            'attendance_start_at' => '09:00:00',
            'attendance_finish_at' => '18:00:00',
            'attendance_status_id' => 4,
        ]);

        // ステータスが退勤済みになっているか確認
        $response = $this->actingAs($loginUser)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('退勤済');

        Carbon::setTestNow();
    }

    // 退勤時刻が勤怠一覧画面で確認できる
    public function test_attendance_data_on_the_list(){

        $loginUser = User::factory()->create();

        // 出勤中の勤怠レコードを作る
        \App\Models\Attendance::create([
            'user_id' => $loginUser->id,
            'attendance_date' => Carbon::today(),
            'attendance_start_at' => Carbon::parse('09:00:00'),
            'attendance_status_id' => 2, // 出勤中
        ]);

        // 退勤処理
        Carbon::setTestNow(today()->setTime(18, 0));
        $this->actingAs($loginUser)->patch('/attendance/finish')->assertStatus(302);

        // 一覧で確認
        $response = $this->actingAs($loginUser)->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSeeInOrder([
            today()->isoFormat('MM/DD(ddd)'),
            '09:00',
            '18:00',
        ]);

        Carbon::setTestNow();
    }
}
