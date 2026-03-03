<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void{
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    //ログイン機能
    public function test_login_user(){
        $user = User::find(4);

        $response = $this->post('/login', [
            'email' => "manager1@gmail.com",
            'password' => "password",
            'is_admin_login' => 1,
        ]);

        $response->assertRedirect('/admin/attendance/list');
        $this->assertAuthenticatedAs($user);
    }

    //メールアドレスが未入力の場合、バリデーションメッセージが表示される
    public function test_login_user_validate_email(){
        $response = $this->post('/login', [
            'email' => "",
            'password' => "password",
            'is_admin_login' => 1,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $errors = session('errors');
        $this->assertEquals('メールアドレスを入力してください', $errors->first('email'));
    }

    //パスワードが未入力の場合、バリデーションメッセージが表示される
    public function test_login_user_validate_password(){
        $response = $this->post('/login', [
            'email' => "manager1@gmail.com",
            'password' => "",
            'is_admin_login' => 1,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');

        $errors = session('errors');
        $this->assertEquals('パスワードを入力してください', $errors->first('password'));
    }

    //登録内容と一致しない場合、バリデーションメッセージが表示される
    public function test_login_user_validate_user(){
        $response = $this->post('/login', [
            'email' => "manager1@gmail.com",
            'password' => "password123",
            'is_admin_login' => 1,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $errors = session('errors');
        $this->assertEquals('ログイン情報が登録されていません', $errors->first('email'));
    }
}