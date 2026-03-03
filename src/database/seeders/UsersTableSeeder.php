<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){
        $param = [
            'name' => '一般ユーザー1',
            'email' => 'ippan1@gmail.com',
            'password' => Hash::make('password'),
            'manager_flg' => '0',
            'email_verified_at' => Carbon::now(),
        ];
        User::create($param);

        $param = [
            'name' => '一般ユーザー2',
            'email' => 'ippan2@gmail.com',
            'password' => Hash::make('password'),
            'manager_flg' => '0',
            'email_verified_at' => Carbon::now(),
        ];
        User::create($param);

        $param = [
            'name' => '一般ユーザー3',
            'email' => 'ippan3@gmail.com',
            'password' => Hash::make('password'),
            'manager_flg' => '0',
            'email_verified_at' => Carbon::now(),
        ];
        User::create($param);

        $param = [
            'name' => '管理者ユーザー1',
            'email' => 'manager1@gmail.com',
            'password' => Hash::make('password'),
            'manager_flg' => '1',
            'email_verified_at' => Carbon::now(),
        ];
        User::create($param);

        $param = [
            'name' => '管理者ユーザー2',
            'email' => 'manager2@gmail.com',
            'password' => Hash::make('password'),
            'manager_flg' => '1',
            'email_verified_at' => Carbon::now(),
        ];
        User::create($param);
    }
}
