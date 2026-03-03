<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AdminAttendanceDetailModifyTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void{
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    // 勤怠詳細画面に表示されるデータが選択したものになっている
    public function test_admin_display_attendance_detail(){
        $user = User::factory()->create();
        $adminUser = User::factory()->create([
            'manager_flg' => 1,
        ]);

        // テストデータ作成
        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
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
        $response = $this->actingAs($adminUser)->get('/admin/attendance/' . $attendance->id);
        $response->assertStatus(200);

        // 詳細画面に勤怠情報が表示される
        $response->assertSee($user->name); // 名前
        $response->assertSee('2026年');         // 年
        $response->assertSee('2月26日');        // 日付
        $response->assertSee('09:00');          // 出勤
        $response->assertSee('18:00');          // 退勤
        $response->assertSee('12:00');          // 休憩開始
        $response->assertSee('13:00');          // 休憩終了
    }

    // 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_admin_display_validate_message_when_start_time_is_later_than_finish_time(){

        $user = User::factory()->create();
        $adminUser = User::factory()->create([
            'manager_flg' => 1,
        ]);

        // テストデータ作成
        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::parse('2026-02-26'),
            'attendance_start_at' => '09:00',
            'attendance_status_id' => 2,
        ]);

        // 詳細を表示
        $response = $this->actingAs($adminUser)->get('/admin/attendance/' . $attendance->id);
        $response->assertStatus(200);

        // 出勤時間の前に終了時間を設定する
        $response = $this->actingAs($adminUser)->post('/admin/attendance/modify/' . $attendance->id, [
            'attendance_start_at'  => '09:00',
            'attendance_finish_at' => '08:00',
            'attendance_status_id' => 4,
        ]);

        // エラーメッセージを表示
        $response->assertStatus(302);
        $response->assertSessionHasErrors('attendance_finish_at');

        $errors = session('errors');
        $this->assertEquals('出勤時間もしくは退勤時間が不適切な値です', $errors->first('attendance_finish_at'));
    }

    // 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_admin_display_validate_message_when_start_breaktime_is_later_than_finish_time(){
        $user = User::factory()->create();
        $adminUser = User::factory()->create([
            'manager_flg' => 1,
        ]);

        // テストデータ作成
        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::parse('2026-02-26'),
            'attendance_start_at' => '09:00',
            'attendance_finish_at' => '18:00',
            'attendance_status_id' => 4,
        ]);

        // 詳細を表示
        $response = $this->actingAs($adminUser)->get('/admin/attendance/' . $attendance->id);
        $response->assertStatus(200); // 成功

        // 出勤時間の前に終了時間を設定する
        $response = $this->actingAs($adminUser)->post('/admin/attendance/modify/' . $attendance->id, [
            'attendance_start_at'  => '09:00',
            'attendance_finish_at' => '18:00',
            'remarks' => 'テスト',
            'attendance_status_id' => 3,

            'breaks' => [
                [
                    'start'  => '19:00', // 勤務終了時間より後
                ],
            ],
        ]);

        // エラーメッセージを表示
        $response->assertStatus(302); // 失敗
        $response->assertSessionHasErrors('breaks.0.start');

        $this->assertEquals(
            '休憩時間が不適切な値です',
            session('errors')->first('breaks.0.start')
        );

        // 申請データが作成されていないことを確認
        $this->assertDatabaseMissing('applications', [
            'attendance_id' => $attendance->id,
        ]);
    }



    // 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_admin_display_validate_message_when_finish_breaktime_is_later_than_finish_time(){
        $user = User::factory()->create();
        $adminUser = User::factory()->create([
            'manager_flg' => 1,
        ]);

        // テストデータ作成
        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::parse('2026-02-26'),
            'attendance_start_at' => '09:00',
            'attendance_finish_at' => '18:00',
            'attendance_status_id' => 4,
        ]);

        // 詳細を表示
        $response = $this->actingAs($adminUser)->get('/admin/attendance/' . $attendance->id);
        $response->assertStatus(200); // 成功

        // 出勤時間の前に終了時間を設定する
        $response = $this->actingAs($adminUser)->post('/admin/attendance/modify/' . $attendance->id, [
            'attendance_start_at'  => '09:00',
            'attendance_finish_at' => '18:00',
            'remarks' => 'テスト',
            'attendance_status_id' => 3,

            'breaks' => [
                [
                    'start'  => '12:00',
                    'finish'  => '19:00', // 勤務終了時間より後
                ],
            ],
        ]);

        // エラーメッセージを表示
        $response->assertStatus(302); // 失敗
        $response->assertSessionHasErrors('breaks.0.finish');

        $this->assertEquals(
            '休憩時間もしくは退勤時間が不適切な値です',
            session('errors')->first('breaks.0.finish')
        );

        // 申請データが作成されていないことを確認
        $this->assertDatabaseMissing('applications', [
            'attendance_id' => $attendance->id,
        ]);
    }

    // 備考欄が未入力の場合のエラーメッセージが表示される
    public function test_admin_display_validate_message_for_remarks(){
        $user = User::factory()->create();
        $adminUser = User::factory()->create([
            'manager_flg' => 1,
        ]);

        // テストデータ作成
        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::parse('2026-02-26'),
            'attendance_start_at' => '09:00',
            'attendance_finish_at' => '18:00',
            'attendance_status_id' => 4,
        ]);

        // 詳細を表示
        $response = $this->actingAs($adminUser)->get('/admin/attendance/' . $attendance->id);
        $response->assertStatus(200); // 成功

        $response = $this->actingAs($adminUser)->post('/admin/attendance/modify/' . $attendance->id, [
            'remarks' => '',  //備考欄が空
        ]);

        // エラーメッセージを表示
        $response->assertStatus(302); // 失敗
        $response->assertSessionHasErrors('remarks');

        $this->assertEquals(
            '備考を記入してください',
            session('errors')->first('remarks')
        );

        // 申請データが作成されていないことを確認
        $this->assertDatabaseMissing('applications', [
            'attendance_id' => $attendance->id,
        ]);
    }
}
