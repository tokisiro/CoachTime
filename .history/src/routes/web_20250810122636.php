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

//一般ユーザー会員登録
Route::get('/register', function () {
    return view('normal.register');
});

//一般ユーザーログイン画面
Route::get('/login', function () {
    return view('normal.login');
});

Route::get('/email', function () {
    return view('normal.email');
});

Route::get('/attendance', function () {
    return view('normal.attendance_register');
});

Route::get('/attendance/list', function () {
    return view('normal.attendance_list');
});

Route::get('/admin/login', function () {
    return view('admin.admin_login');
});

Route::get('/admin/attendance/list', function () {
    return view('admin.admin_attendance_list');
});

Route::get('/admin/attendances/id', function () {
    return view('admin.admin_attendance_details');
});

Route::get('/admin/users', function () {
    return view('admin.staff_list');
});

Route::get('/admin/users/user/attendances',function () {
    return view('admin.staff_attendance_list');
});


