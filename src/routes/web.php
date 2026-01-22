<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/login', function () {
    return view('auth/login');
});

Route::get('/register', function () {
    return view('auth/register');
});

Route::get('/auth/mail', function () {
    return view('auth/mail');
});

// 一般スタッフ
Route::get('/stamping', function () {
    return view('staff/attendance-stamp');
});

Route::get('/request-list', function () {
    return view('staff/request-list');
});

Route::get('/attendance-list', function () {
    return view('attendance-list');
});


