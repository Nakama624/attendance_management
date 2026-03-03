<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;


class StampingStartWorkTest extends TestCase
{

    use DatabaseMigrations;

    protected function setUp(): void{
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    // 出勤ボタンが正しく機能する
    public function test_attendance_button_works_properly(){

        $loginUser = User::factory()->create();

        Carbon::setTestNow(Carbon::parse('2026-02-26 09:00:00'));

        // ボタン表示確認
        $response = $this->actingAs($loginUser)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee(
            '<button class="attendance__btn--black" type="submit">出勤</button>',
            false
        );

        // ボタン押下
        $response = $this->actingAs($loginUser)->post('/attendance/start');
        $response->assertStatus(302); // リダイレクト

        // DB確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $loginUser->id,
            'attendance_date' => '2026-02-26',
            'attendance_start_at' => '09:00:00',
            'attendance_status_id' => 2,
        ]);

        // ステータスが出勤中になっているか確認
        $response = $this->actingAs($loginUser)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    // 出勤は一日一回のみできる
    public function test_attendance_button_push_once_a_day(){
        $loginUser = User::factory()->create();

        Carbon::setTestNow('2026-02-27 09:00:00');

        // 1回目
        $this->actingAs($loginUser)->post('/attendance/start')->assertStatus(302);

        // 2回目
        Carbon::setTestNow('2026-02-27 10:00:00');
        $this->actingAs($loginUser)->post('/attendance/start')->assertStatus(302);

        // 当日分は1件だけ
        $this->assertSame(
            1,
            \App\Models\Attendance::where('user_id', $loginUser->id)
                ->whereDate('attendance_date', '2026-02-27')
                ->count()
        );

        // 画面を再取得
        $response = $this->actingAs($loginUser)->get('/attendance');

        // 出勤ボタンが表示されていない
        $response->assertDontSee(
            '<button class="attendance__btn--black" type="submit">出勤</button>',
            false
        );
    }

    // 退勤したユーザーには出勤ボタンを表示しない
    public function test_start_button_is_not_displayed_when_finished_work(){
        $loginUser = User::factory()->create();

        // 退勤済の勤怠レコードを作る
        \App\Models\Attendance::create([
            'user_id' => $loginUser->id,
            'attendance_date' => Carbon::today(),
            'attendance_start_at' => Carbon::parse('09:00:00'),
            'attendance_finish_at' => Carbon::parse('18:00:00'),
            'attendance_status_id' => 4, // 退勤済
        ]);

        $response = $this->actingAs($loginUser)->get('/attendance');
        $response->assertStatus(200);
        $response->assertDontSee('出勤');
        $response->assertSee('お疲れ様でした。');
    }

    // 出勤時刻が勤怠一覧画面で確認できる
    public function test_attendance_data_on_the_list(){

        $loginUser = User::factory()->create();

        Carbon::setTestNow('2026-02-27 09:00:00');

        // 出勤処理
        $this->actingAs($loginUser)->post('/attendance/start')->assertStatus(302);

        // 一覧で確認
        $response = $this->actingAs($loginUser)->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSeeInOrder([
            '02/27(金)',
            '09:00',
        ]);
    }
}
