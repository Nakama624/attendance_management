<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AttendanceStatusTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void{
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    // 勤務外の場合、勤怠ステータスが正しく表示される（ステータス1）
    public function test_attendance_status_off_duty(){
        $loginUser = User::find(4);

        $response = $this->actingAs($loginUser)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }


    // 出勤中の場合、勤怠ステータスが正しく表示される（ステータス2）
    public function test_attendance_status_at_working(){
        $loginUser = User::find(1);

        $response = $this->actingAs($loginUser)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    // 休憩中の場合、勤怠ステータスが正しく表示される（ステータス3）
    public function test_attendance_status_on_a_break(){
        $loginUser = User::find(3);

        $response = $this->actingAs($loginUser)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    // 退勤済の場合、勤怠ステータスが正しく表示される（ステータス4）
    public function test_attendance_status_after_work(){
        $loginUser = User::find(2);

        $response = $this->actingAs($loginUser)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }
}
