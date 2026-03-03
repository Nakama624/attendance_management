<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;

class StampingBreakTimeTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void{
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    // 休憩ボタンが正しく機能する
    // 休憩戻ボタンが正しく機能する
    public function test_break_button_works_properly(){

        $loginUser = User::factory()->create();

        Carbon::setTestNow(Carbon::parse('2026-02-26 12:00:00'));

        // 出勤中の勤怠レコードを作る
        \App\Models\Attendance::create([
            'user_id' => $loginUser->id,
            'attendance_date' => Carbon::parse('2026-02-26'),
            'attendance_start_at' => Carbon::parse('2026-02-26 09:00:00'),
            'attendance_status_id' => 2, // 出勤中
        ]);

        // 休憩入ボタン表示確認
        $response = $this->actingAs($loginUser)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee(
            '<button class="attendance__btn--white" type="submit">休憩入</button>',
            false
        );

        // 休憩入ボタン押下
        $this->actingAs($loginUser)->post('/attendance/start_break_time')
            ->assertStatus(302); // リダイレクト

        // 休憩開始後の勤怠レコードを取得
        $attendance = \App\Models\Attendance::where('user_id', $loginUser->id)
            ->whereDate('attendance_date', '2026-02-26')
            ->first();

        // 勤務テーブル確認
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'attendance_date' => '2026-02-26',
            'attendance_status_id' => 3,
        ]);

        // 休憩テーブル確認
        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
            'break_time_date' => '2026-02-26',
        ]);

        // ステータスが休憩中、休憩戻ボタンが表示されてるか確認
        $response = $this->actingAs($loginUser)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('休憩中');
        $response->assertSee(
            '<button class="attendance__btn--white" type="submit">休憩戻</button>',
            false
        );

        // 休憩入ボタン押下
        Carbon::setTestNow(Carbon::parse('2026-02-26 13:00:00'));
        $this->actingAs($loginUser)->patch('/attendance/finish_break_time')
            ->assertStatus(302); // リダイレクト

        // 休憩終了後の勤怠レコードを取得
        $attendance = \App\Models\Attendance::where('user_id', $loginUser->id)
            ->whereDate('attendance_date', '2026-02-26')
            ->first();

        // 勤務テーブル確認
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'attendance_date' => '2026-02-26',
            'attendance_status_id' => 2,
        ]);

        // 休憩テーブル確認
        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
            'break_time_date' => '2026-02-26',
        ]);

        // ステータスが出勤中、休憩入/退勤ボタンが表示されてるか確認
        $response = $this->actingAs($loginUser)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤中');
        $response->assertSee(
            '<button class="attendance__btn--white" type="submit">休憩入</button>',
            false
        );
        $response->assertSee(
            '<button class="attendance__btn--black" type="submit">退勤</button>',
            false
        );
    }

    // 休憩は一日に何回でもできる
    // 休憩戻は一日に何回でもできる
    public function test_attendance_button_push_once_a_day(){
        $loginUser = User::factory()->create();

        Carbon::setTestNow('2026-02-26 9:00:00');

        // 出勤中レコードを作成
        $attendance = \App\Models\Attendance::create([
            'user_id' => $loginUser->id,
            'attendance_date' => Carbon::today(),
            'attendance_start_at' => Carbon::now(),
            'attendance_status_id' => 2, // 出勤中
        ]);

        // 休憩入
        Carbon::setTestNow('2026-02-26 12:00:00');
        $this->actingAs($loginUser)->post('/attendance/start_break_time')->assertStatus(302);
        // 休憩戻
        Carbon::setTestNow('2026-02-26 13:00:00');
        $this->actingAs($loginUser)->patch('/attendance/finish_break_time')->assertStatus(302);

        // 2回目 休憩入
        Carbon::setTestNow('2026-02-26 15:00:00');
        $this->actingAs($loginUser)->post('/attendance/start_break_time')->assertStatus(302);
        // 2回目 休憩戻
        Carbon::setTestNow('2026-02-26 15:15:00');
        $this->actingAs($loginUser)->patch('/attendance/finish_break_time')->assertStatus(302);

        // breakテーブルが2件になっている
        $this->assertSame(
            2,
            \App\Models\Breaktime::where('attendance_id', $attendance->id)->count()
        );

        // 画面を再取得
        $response = $this->actingAs($loginUser)->get('/attendance');

        // 出勤ボタンが表示されていない
        $response->assertSee(
            '<button class="attendance__btn--white" type="submit">休憩入</button>',
            false
        );
    }


    // 休憩時刻が勤怠一覧画面で確認できる
    public function test_attendance_breaktimes_data_on_the_list(){

        $loginUser = User::factory()->create();

        Carbon::setTestNow('2026-02-26 09:00:00');

        // 出勤処理
        $this->actingAs($loginUser)->post('/attendance/start')->assertStatus(302);

        // 休憩入
        Carbon::setTestNow('2026-02-26 12:00:00');
        $this->actingAs($loginUser)->post('/attendance/start_break_time')->assertStatus(302);

        // 休憩戻
        Carbon::setTestNow('2026-02-26 13:00:00');
        $this->actingAs($loginUser)->patch('/attendance/finish_break_time')->assertStatus(302);


        // 一覧で確認
        $response = $this->actingAs($loginUser)->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSeeInOrder([
            '02/26(木)',
            '09:00',
            '',
            '1:00',
        ]);
    }

}
