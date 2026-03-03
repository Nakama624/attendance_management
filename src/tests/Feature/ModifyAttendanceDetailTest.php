<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ModifyAttendanceDetailTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void{
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    // 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_display_validate_message_when_start_time_is_later_than_finish_time(){
        $loginUser = User::factory()->create();

        // テストデータ作成
        $attendance = \App\Models\Attendance::create([
            'user_id' => $loginUser->id,
            'attendance_date' => Carbon::parse('2026-02-26'),
            'attendance_start_at' => '09:00',
            'attendance_status_id' => 2,
        ]);

        // 詳細を表示
        $response = $this->actingAs($loginUser)->get('/attendance/detail/' . $attendance->id);
        $response->assertStatus(200);

        // 出勤時間の前に終了時間を設定する
        $response = $this->actingAs($loginUser)->post('/attendance/modify/' . $attendance->id, [
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
    public function test_display_validate_message_when_start_breaktime_is_later_than_finish_time(){
        $loginUser = User::factory()->create();

        // テストデータ作成
        $attendance = \App\Models\Attendance::create([
            'user_id' => $loginUser->id,
            'attendance_date' => Carbon::parse('2026-02-26'),
            'attendance_start_at' => '09:00',
            'attendance_finish_at' => '18:00',
            'attendance_status_id' => 4,
        ]);

        // 詳細を表示
        $response = $this->actingAs($loginUser)->get('/attendance/detail/' . $attendance->id);
        $response->assertStatus(200); // 成功

        // 出勤時間の前に終了時間を設定する
        $response = $this->actingAs($loginUser)->post('/attendance/modify/' . $attendance->id, [
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
    public function test_display_validate_message_when_finish_breaktime_is_later_than_finish_time(){
        $loginUser = User::factory()->create();

        // テストデータ作成
        $attendance = \App\Models\Attendance::create([
            'user_id' => $loginUser->id,
            'attendance_date' => Carbon::parse('2026-02-26'),
            'attendance_start_at' => '09:00',
            'attendance_finish_at' => '18:00',
            'attendance_status_id' => 4,
        ]);

        // 詳細を表示
        $response = $this->actingAs($loginUser)->get('/attendance/detail/' . $attendance->id);
        $response->assertStatus(200); // 成功

        // 出勤時間の前に終了時間を設定する
        $response = $this->actingAs($loginUser)->post('/attendance/modify/' . $attendance->id, [
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
    public function test_display_validate_message_for_remarks(){
        $loginUser = User::factory()->create();

        // テストデータ作成
        $attendance = \App\Models\Attendance::create([
            'user_id' => $loginUser->id,
            'attendance_date' => Carbon::parse('2026-02-26'),
            'attendance_start_at' => '09:00',
            'attendance_finish_at' => '18:00',
            'attendance_status_id' => 4,
        ]);

        // 詳細を表示
        $response = $this->actingAs($loginUser)->get('/attendance/detail/' . $attendance->id);
        $response->assertStatus(200); // 成功

        $response = $this->actingAs($loginUser)->post('/attendance/modify/' . $attendance->id, [
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

    // 修正申請処理が実行される
    public function test_the_application_is_created_successfully(){

        // 一般ユーザー
        $loginUser = User::factory()->create();
        // 管理者
        $adminUser = User::factory()->create([
            'manager_flg' => 1,
        ]);

        // テストデータ作成
        $attendance = \App\Models\Attendance::create([
            'user_id' => $loginUser->id,
            'attendance_date' => Carbon::parse('2026-02-26'),
            'attendance_start_at' => '09:00',
            'attendance_finish_at' => '18:00',
            'attendance_status_id' => 4,
        ]);

        // 詳細を表示
        $response = $this->actingAs($loginUser)->get('/attendance/detail/' . $attendance->id);
        $response->assertStatus(200); // 成功

        // 出勤時間の前に終了時間を設定する
        Carbon::setTestNow(Carbon::parse('2026-02-27 10:00:00'));
        $response = $this->actingAs($loginUser)->post('/attendance/modify/' . $attendance->id, [
            'attendance_start_at'  => '09:00',
            'attendance_finish_at' => '18:00',
            'remarks' => 'テスト',

            'breaks' => [
                [
                    'start'  => '12:00', // 正常な時間
                    'finish'  => '13:00', // 正常な時間
                ],
            ],
        ]);

        $response->assertStatus(200); // 成功
        $response->assertSee('* 承認待ちのため修正はできません。');

        //申請データが作成されているか確認
        $this->assertDatabaseHas('applications', [
            'user_id'       => $loginUser->id,
            'attendance_id' => $attendance->id,
            'application_status_id' => 1, // 承認待ち
        ]);

        // pending_applications と紐付くか確認
        $application = \App\Models\Application::where('user_id', $loginUser->id)
            ->where('attendance_id', $attendance->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($application);

        $this->assertDatabaseHas('pending_applications', [
            'application_id' => $application->id,
            'attendance_id'      => $attendance->id,
        ]);

        // 一般ユーザーが承認待ちの申請一覧を開く
        $list = $this->actingAs($loginUser)->get('/stamp_correction_request/list?page=pending');
        $list->assertStatus(200);

        // 申請一覧
        $list->assertSee('承認待ち');
        $list->assertSee($loginUser->name);
        $list->assertSee('2026/02/26');
        $list->assertSee('テスト');
        $list->assertSee('2026/02/27');

        // 管理者が承認待ちの申請一覧を開く
        $adminList = $this->actingAs($adminUser)->get('/stamp_correction_request/list?page=pending');
        $adminList->assertStatus(200);

        // 申請一覧
        $adminList->assertSee('承認待ち');
        $adminList->assertSee($loginUser->name);
        $adminList->assertSee('2026/02/26');
        $adminList->assertSee('テスト');
        $adminList->assertSee('2026/02/27');
    }

    // 「承認待ち」にログインユーザーが行った申請が全て表示されていること
    public function test_all_applications_display_on_the_pending_list(){
        $loginUser = User::factory()->create();

        // 1つめのテストデータ作成
        $attendance1 = \App\Models\Attendance::create([
            'user_id' => $loginUser->id,
            'attendance_date' => Carbon::parse('2026-02-20'),
            'attendance_start_at' => '09:00',
            'attendance_finish_at' => '18:00',
            'attendance_status_id' => 4,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-02-24 10:00:00'));
        $response = $this->actingAs($loginUser)->post('/attendance/modify/' . $attendance1->id, [
            'attendance_start_at'  => '09:00',
            'attendance_finish_at' => '18:00',
            'remarks' => 'テスト',

            'breaks' => [
                [
                    'start'  => '12:00', // 正常な時間
                    'finish'  => '13:00', // 正常な時間
                ],
            ],
        ]);

        // 2つめのテストデータ作成
        $attendance2 = \App\Models\Attendance::create([
            'user_id' => $loginUser->id,
            'attendance_date' => Carbon::parse('2026-02-21'),
            'attendance_start_at' => '10:00',
            'attendance_finish_at' => '19:00',
            'attendance_status_id' => 4,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-02-25 10:00:00'));
        $response = $this->actingAs($loginUser)->post('/attendance/modify/' . $attendance2->id, [
            'attendance_start_at'  => '10:00',
            'attendance_finish_at' => '17:00',
            'remarks' => 'テストテスト',

            'breaks' => [
                [
                    'start'  => '12:30', // 正常な時間
                    'finish'  => '13:30', // 正常な時間
                ],
            ],
        ]);

        // 3つめのテストデータ作成
        $attendance3 = \App\Models\Attendance::create([
            'user_id' => $loginUser->id,
            'attendance_date' => Carbon::parse('2026-02-22'),
            'attendance_start_at' => '11:00',
            'attendance_finish_at' => '19:00',
            'attendance_status_id' => 4,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-02-26 10:00:00'));
        $response = $this->actingAs($loginUser)->post('/attendance/modify/' . $attendance3->id, [
            'attendance_start_at'  => '10:00',
            'attendance_finish_at' => '21:00',
            'remarks' => 'テストテストテスト',

            'breaks' => [
                [
                    'start'  => '13:30', // 正常な時間
                    'finish'  => '15:30', // 正常な時間
                ],
            ],
        ]);

        // 一般ユーザーが承認待ちの申請一覧を開く
        $list = $this->actingAs($loginUser)->get('/stamp_correction_request/list?page=pending');
        $list->assertStatus(200);

        // 申請一覧
        $list->assertStatus(200);
        $list->assertSeeInOrder([
            '承認待ち',
            $loginUser->name,
            '2026/02/20',
            'テスト',
            '2026/02/24',
        ]);
        $list->assertSeeInOrder([
            '承認待ち',
            $loginUser->name,
            '2026/02/21',
            'テストテスト',
            '2026/02/25',
        ]);
        $list->assertSeeInOrder([
            '承認待ち',
            $loginUser->name,
            '2026/02/22',
            'テストテストテスト',
            '2026/02/26',
        ]);

        Carbon::setTestNow(); // リセット
    }

    // 「承認済み」に管理者が承認した修正申請が全て表示されている
    public function test_display_all_applications_on_the_approved_list(){
        Carbon::setTestNow(); // 念のためリセット

        // 一般ユーザー
        $loginUser = User::factory()->create();

        // 管理者
        $adminUser = User::factory()->create([
            'manager_flg' => 1,
        ]);

        $applicationIds = [];

        // ---------- 1件目 ----------
        $attendance1 = \App\Models\Attendance::create([
            'user_id' => $loginUser->id,
            'attendance_date' => Carbon::parse('2026-02-20'),
            'attendance_start_at' => '09:00',
            'attendance_finish_at' => '18:00',
            'attendance_status_id' => 4,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-02-24 10:00:00'));
        $this->actingAs($loginUser)->post('/attendance/modify/' . $attendance1->id, [
            'attendance_start_at'  => '09:00',
            'attendance_finish_at' => '18:00',
            'remarks' => 'テスト',
            'breaks' => [
                ['start' => '12:00', 'finish' => '13:00'],
            ],
        ])->assertStatus(200);

        $app1 = \App\Models\Application::where('user_id', $loginUser->id)
            ->where('attendance_id', $attendance1->id)
            ->latest('id')
            ->firstOrFail();

        $applicationIds[] = $app1->id;

        // ---------- 2件目 ----------
        $attendance2 = \App\Models\Attendance::create([
            'user_id' => $loginUser->id,
            'attendance_date' => Carbon::parse('2026-02-21'),
            'attendance_start_at' => '10:00',
            'attendance_finish_at' => '19:00',
            'attendance_status_id' => 4,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-02-25 10:00:00'));
        $this->actingAs($loginUser)->post('/attendance/modify/' . $attendance2->id, [
            'attendance_start_at'  => '10:00',
            'attendance_finish_at' => '17:00',
            'remarks' => 'テストテスト',
            'breaks' => [
                ['start' => '12:30', 'finish' => '13:30'],
            ],
        ])->assertStatus(200);

        $app2 = \App\Models\Application::where('user_id', $loginUser->id)
            ->where('attendance_id', $attendance2->id)
            ->latest('id')
            ->firstOrFail();

        $applicationIds[] = $app2->id;

        // ---------- 3件目 ----------
        $attendance3 = \App\Models\Attendance::create([
            'user_id' => $loginUser->id,
            'attendance_date' => Carbon::parse('2026-02-22'),
            'attendance_start_at' => '11:00',
            'attendance_finish_at' => '19:00',
            'attendance_status_id' => 4,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-02-26 10:00:00'));
        $this->actingAs($loginUser)->post('/attendance/modify/' . $attendance3->id, [
            'attendance_start_at'  => '10:00',
            'attendance_finish_at' => '21:00',
            'remarks' => 'テストテストテスト',
            'breaks' => [
                ['start' => '13:30', 'finish' => '15:30'],
            ],
        ])->assertStatus(200);

        $app3 = \App\Models\Application::where('user_id', $loginUser->id)
            ->where('attendance_id', $attendance3->id)
            ->latest('id')
            ->firstOrFail();

        $applicationIds[] = $app3->id;

        // 承認
        Carbon::setTestNow(Carbon::parse('2026-02-27 13:00:00'));
        foreach ($applicationIds as $applicationId) {
            $this->actingAs($adminUser)
                ->patch(route('stamp_correction_request.approved', ['application_id' => $applicationId]))
                ->assertStatus(302);
        }

        // 一覧
        $list = $this->actingAs($loginUser)->get('/stamp_correction_request/list?page=approved');
        $list->assertStatus(200);

        $list->assertSee('承認済み');
        $list->assertSee($loginUser->name);

        $list->assertSee('2026/02/20');
        $list->assertSee('テスト');
        $list->assertSee('2026/02/24');

        $list->assertSee('2026/02/21');
        $list->assertSee('テストテスト');
        $list->assertSee('2026/02/25');

        $list->assertSee('2026/02/22');
        $list->assertSee('テストテストテスト');
        $list->assertSee('2026/02/26');

        Carbon::setTestNow(); // リセット
    }

    // 各申請の「詳細」を押下すると勤怠詳細画面に遷移する
    public function test_display_detail_after_push_detail_button(){
        Carbon::setTestNow(); // 念のためリセット

        // 一般ユーザー
        $loginUser = User::factory()->create();

        // テストデータ作成
        $attendance = \App\Models\Attendance::create([
            'user_id' => $loginUser->id,
            'attendance_date' => Carbon::parse('2026-02-26'),
            'attendance_start_at' => '09:00',
            'attendance_finish_at' => '18:00',
            'attendance_status_id' => 4,
        ]);

        // 詳細を表示
        $response = $this->actingAs($loginUser)->get('/attendance/detail/' . $attendance->id);
        $response->assertStatus(200); // 成功

        // 出勤時間の前に終了時間を設定する
        Carbon::setTestNow(Carbon::parse('2026-02-27 10:00:00'));
        $response = $this->actingAs($loginUser)->post('/attendance/modify/' . $attendance->id, [
            'attendance_start_at'  => '09:00',
            'attendance_finish_at' => '18:00',
            'remarks' => 'テスト',

            'breaks' => [
                [
                    'start'  => '12:00', // 正常な時間
                    'finish'  => '13:00', // 正常な時間
                ],
            ],
        ]);

        $response->assertStatus(200); // 成功
        $response->assertSee('* 承認待ちのため修正はできません。');

        //申請データが作成されているか確認
        $this->assertDatabaseHas('applications', [
            'user_id'       => $loginUser->id,
            'attendance_id' => $attendance->id,
            'application_status_id' => 1, // 承認待ち
        ]);

        // pending_applications と紐付くか確認
        $application = \App\Models\Application::where('user_id', $loginUser->id)
            ->where('attendance_id', $attendance->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($application);

        $this->assertDatabaseHas('pending_applications', [
            'application_id' => $application->id,
            'attendance_id'      => $attendance->id,
        ]);

        // 一般ユーザーが承認待ちの申請一覧を開く
        $list = $this->actingAs($loginUser)->get('/stamp_correction_request/list?page=pending');
        $list->assertStatus(200);

        // 申請一覧
        $list->assertSee('承認待ち');
        $list->assertSee($loginUser->name);
        $list->assertSee('2026/02/26');
        $list->assertSee('テスト');
        $list->assertSee('2026/02/27');

        // 詳細から勤怠詳細画面に遷移する
        $detail = $this->actingAs($loginUser)->get('/attendance/pending/' . $application->id);
        $detail->assertStatus(200);

        // 詳細画面に修正後の情報が表示される
        $detail->assertSee($loginUser->name); // 名前
        $detail->assertSee('2026年');         // 年
        $detail->assertSee('2月26日');        // 日付
        $detail->assertSee('09:00');          // 出勤
        $detail->assertSee('18:00');          // 退勤
        $detail->assertSee('12:00');          // 休憩開始
        $detail->assertSee('13:00');          // 休憩終了

    }
}