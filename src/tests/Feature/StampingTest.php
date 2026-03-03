<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;


class StampingTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void{
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    // 現在の日時情報がUIと同じ形式で出力されている
    public function test_current_time_on_stamping_view(){
        $loginUser = User::find(2);

        Carbon::setTestNow(Carbon::create(2026, 2, 25, 10, 30, 0, 'Asia/Tokyo'));

        $response = $this->actingAs($loginUser)->get('/attendance');

        $response->assertStatus(200);

        $date = Carbon::now()->isoFormat('YYYY年M月D日(ddd)');
        $time = Carbon::now()->format('H:i');

        $response->assertSee($date);
        $response->assertSee($time);

        Carbon::setTestNow();
    }
}
