<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AdminAttendanceListEachUsersTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void{
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    // 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる
    public function test_admin_display_staff_list(){

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        $adminUser = User::factory()->create([
            'manager_flg' => 1,
        ]);

        $response = $this->actingAs($adminUser)->get('/admin/staff/list');
        $response->assertStatus(200);

        $response->assertSeeInOrder([
            $user1->name,
            $user1->email,
        ]);

        $response->assertSeeInOrder([
            $user2->name,
            $user2->email,
        ]);

        $response->assertSeeInOrder([
            $user3->name,
            $user3->email,
        ]);
    }

    // ユーザーの勤怠情報が正しく表示される
    public function test_admin_display_attendance_list(){
        Carbon::setTestNow('2026-02-26');

        $user = User::find(1);
        $adminUser = User::factory()->create([
            'manager_flg' => 1,
        ]);

        $response = $this->actingAs($adminUser)->get('/admin/attendance/staff/'. $user->id);
        $response->assertStatus(200);

        $response->assertSeeInOrder([
            '02/23(月)',
            '09:00',
            '18:00',
        ]);
        $response->assertSeeInOrder([
            '02/24(火)',
            '10:00',
            '19:00',
        ]);
        $response->assertSeeInOrder([
            '02/25(水)',
            '09:30',
            '18:30',
        ]);
    }

    // 「前月」を押下した時に表示月の前月の情報が表示される
    public function test_admin_display_last_month_on_the_attendance_list(){
        Carbon::setTestNow('2026-02-26');

        $user = User::factory()->create();
        $adminUser = User::factory()->create([
            'manager_flg' => 1,
        ]);

        $response = $this->actingAs($adminUser)->get('/admin/attendance/staff/'. $user->id);
        $response->assertStatus(200);

        // 前月を押す
        $response = $this->actingAs($adminUser)
            ->get('/admin/attendance/staff/'. $user->id . '?month=2026-01');

        $response->assertStatus(200);
        $response->assertSee('2026/01');
    }

    // 「翌月」を押下した時に表示月の前月の情報が表示される
    public function test_admin_display_next_month_on_the_attendance_list(){
        Carbon::setTestNow('2026-02-26');

        $user = User::factory()->create();
        $adminUser = User::factory()->create([
            'manager_flg' => 1,
        ]);

        $response = $this->actingAs($adminUser)->get('/admin/attendance/staff/'. $user->id);
        $response->assertStatus(200);

        // 翌月を押す
        $response = $this->actingAs($adminUser)
            ->get('/admin/attendance/staff/'. $user->id . '?month=2026-03');

        $response->assertStatus(200);
        $response->assertSee('2026/03');
    }


    // 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
    public function test_admin_transition_to_attendance_detail_from_the_list(){
        $user = User::factory()->create();
        $adminUser = User::factory()->create([
            'manager_flg' => 1,
        ]);

        // テストデータ作成
        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'attendance_date' => Carbon::parse('2026-02-26'),
            'attendance_start_at' => Carbon::parse('09:00:00'),
            'attendance_finish_at' => Carbon::parse('18:00:00'),
            'attendance_status_id' => 4,
        ]);

        // 一覧を表示
        $response = $this->actingAs($adminUser)->get('/admin/attendance/list');
        $response->assertStatus(200);

        // 詳細に遷移
        $response = $this->actingAs($adminUser)->get('/admin/attendance/' . $attendance->id);
        $response->assertStatus(200);


        // 詳細画面に勤怠情報が表示される
        $response->assertSee('2026年');
        $response->assertSee('2月26日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}
