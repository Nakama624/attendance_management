<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AdminAttendanceModifyTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void{
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    // 承認待ちの修正申請が全て表示されている
    public function test_all_applications_display_on_the_pending_list(){
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        $adminUser = User::factory()->create([
            'manager_flg' => 1,
        ]);

        // 1つめのテストデータ作成
        $attendance1 = \App\Models\Attendance::create([
            'user_id' => $user1->id,
            'attendance_date' => Carbon::parse('2026-02-20'),
            'attendance_start_at' => '09:00',
            'attendance_finish_at' => '18:00',
            'attendance_status_id' => 4,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-02-24 10:00:00'));
        $response = $this->actingAs($user1)->post('/attendance/modify/' . $attendance1->id, [
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
            'user_id' => $user2->id,
            'attendance_date' => Carbon::parse('2026-02-21'),
            'attendance_start_at' => '10:00',
            'attendance_finish_at' => '19:00',
            'attendance_status_id' => 4,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-02-25 10:00:00'));
        $response = $this->actingAs($user2)->post('/attendance/modify/' . $attendance2->id, [
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
            'user_id' => $user3->id,
            'attendance_date' => Carbon::parse('2026-02-22'),
            'attendance_start_at' => '11:00',
            'attendance_finish_at' => '19:00',
            'attendance_status_id' => 4,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-02-26 10:00:00'));
        $response = $this->actingAs($user3)->post('/attendance/modify/' . $attendance3->id, [
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

        // 管理者が承認待ちの申請一覧を開く
        $list = $this->actingAs($adminUser)->get('/stamp_correction_request/list?page=pending');
        $list->assertStatus(200);

        // 申請一覧
        $list->assertStatus(200);
        $list->assertSeeInOrder([
            '承認待ち',
            $user1->name,
            '2026/02/20',
            'テスト',
            '2026/02/24',
        ]);
        $list->assertSeeInOrder([
            '承認待ち',
            $user2->name,
            '2026/02/21',
            'テストテスト',
            '2026/02/25',
        ]);
        $list->assertSeeInOrder([
            '承認待ち',
            $user3->name,
            '2026/02/22',
            'テストテストテスト',
            '2026/02/26',
        ]);

        Carbon::setTestNow(); // リセット
    }

    // 承認済みの修正申請が全て表示されている
    public function test_admin_display_all_applications_on_the_approved_list(){
        Carbon::setTestNow(); // 念のためリセット

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        $adminUser = User::factory()->create([
            'manager_flg' => 1,
        ]);

        $applicationIds = [];

        // ---------- 1件目 ----------
        $attendance1 = \App\Models\Attendance::create([
            'user_id' => $user1->id,
            'attendance_date' => Carbon::parse('2026-02-20'),
            'attendance_start_at' => '09:00',
            'attendance_finish_at' => '18:00',
            'attendance_status_id' => 4,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-02-24 10:00:00'));
        $this->actingAs($user1)->post('/attendance/modify/' . $attendance1->id, [
            'attendance_start_at'  => '09:00',
            'attendance_finish_at' => '18:00',
            'remarks' => 'テスト',
            'breaks' => [
                ['start' => '12:00', 'finish' => '13:00'],
            ],
        ])->assertStatus(200);

        $app1 = \App\Models\Application::where('user_id', $user1->id)
            ->where('attendance_id', $attendance1->id)
            ->latest('id')
            ->firstOrFail();

        $applicationIds[] = $app1->id;

        // ---------- 2件目 ----------
        $attendance2 = \App\Models\Attendance::create([
            'user_id' => $user2->id,
            'attendance_date' => Carbon::parse('2026-02-21'),
            'attendance_start_at' => '10:00',
            'attendance_finish_at' => '19:00',
            'attendance_status_id' => 4,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-02-25 10:00:00'));
        $this->actingAs($user2)->post('/attendance/modify/' . $attendance2->id, [
            'attendance_start_at'  => '10:00',
            'attendance_finish_at' => '17:00',
            'remarks' => 'テストテスト',
            'breaks' => [
                ['start' => '12:30', 'finish' => '13:30'],
            ],
        ])->assertStatus(200);

        $app2 = \App\Models\Application::where('user_id', $user2->id)
            ->where('attendance_id', $attendance2->id)
            ->latest('id')
            ->firstOrFail();

        $applicationIds[] = $app2->id;

        // ---------- 3件目 ----------
        $attendance3 = \App\Models\Attendance::create([
            'user_id' => $user3->id,
            'attendance_date' => Carbon::parse('2026-02-22'),
            'attendance_start_at' => '11:00',
            'attendance_finish_at' => '19:00',
            'attendance_status_id' => 4,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-02-26 10:00:00'));
        $this->actingAs($user3)->post('/attendance/modify/' . $attendance3->id, [
            'attendance_start_at'  => '10:00',
            'attendance_finish_at' => '21:00',
            'remarks' => 'テストテストテスト',
            'breaks' => [
                ['start' => '13:30', 'finish' => '15:30'],
            ],
        ])->assertStatus(200);

        $app3 = \App\Models\Application::where('user_id', $user3->id)
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
        $list = $this->actingAs($adminUser)->get('/stamp_correction_request/list?page=approved');
        $list->assertStatus(200);


        $list->assertSeeInOrder([
            '承認済み',
            $user1->name,
            '2026/02/20',
            'テスト',
            '2026/02/24',
        ]);

        $list->assertSeeInOrder([
            '承認済み',
            $user2->name,
            '2026/02/21',
            'テストテスト',
            '2026/02/25',
        ]);

        $list->assertSeeInOrder([
            '承認済み',
            $user3->name,
            '2026/02/22',
            'テストテストテスト',
            '2026/02/26',
        ]);

        Carbon::setTestNow(); // リセット
    }

    // 修正申請の詳細内容が正しく表示されている
    public function test_admin_display_modified_attendance_detail(){
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

        // 修正申請する
        $response = $this->actingAs($user)->post('/attendance/modify/' . $attendance->id, [
            'attendance_start_at' => '09:00',
            'attendance_finish_at' => '20:00',
            'remarks' => 'テスト',
            'breaks' => [
                [
                    'start' => '12:00',
                    'finish' => '13:00',
                ]
            ]
        ]);

        $response->assertStatus(200);
        $response->assertSessionHasNoErrors();

        // pending_applications と紐付くか確認
        $application = \App\Models\Application::where('user_id', $user->id)
            ->where('attendance_id', $attendance->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($application);

        $this->assertDatabaseHas('pending_applications', [
            'application_id' => $application->id,
            'attendance_id'      => $attendance->id,
        ]);

        // 詳細を表示
        $response = $this->actingAs($adminUser)->get('/stamp_correction_request/approve/' . $application->id);
        $response->assertStatus(200);

        // 申請詳細
        $response->assertSee('2026年');
        $response->assertSee('2月26日');
        $response->assertSee('09:00');
        $response->assertSee('20:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee('承認');
    }

    // 修正申請の承認処理が正しく行われる
    public function test_admin_display_approved_attendance_detail(){
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

        // 修正申請する
        $response = $this->actingAs($user)->post('/attendance/modify/' . $attendance->id, [
            'attendance_start_at' => '09:00',
            'attendance_finish_at' => '20:00',
            'remarks' => 'テスト',
            'breaks' => [
                [
                    'start' => '12:00',
                    'finish' => '13:00',
                ]
            ]
        ]);

        $response->assertStatus(200);
        $response->assertSessionHasNoErrors();

        // pending_applications と紐付くか確認
        $application = \App\Models\Application::where('user_id', $user->id)
            ->where('attendance_id', $attendance->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($application);

        $this->assertDatabaseHas('pending_applications', [
            'application_id' => $application->id,
            'attendance_id'      => $attendance->id,
        ]);

        // 詳細を表示
        $response = $this->actingAs($adminUser)->get('/stamp_correction_request/approve/' . $application->id);
        $response->assertStatus(200);


        // 承認する
        $response = $this->actingAs($adminUser)->patch('/stamp_correction_request/approve/' . $application->id);
        $response->assertStatus(302);

        $response->assertRedirect();

        $response = $this->actingAs($adminUser)
            ->get('/stamp_correction_request/approve/' . $application->id);

        $response->assertSee('承認済み');


        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'attendance_finish_at' => '20:00:00', //修正内容
        ]);
    }

}
