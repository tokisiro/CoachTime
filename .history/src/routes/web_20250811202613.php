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

//会員登録(一般)
Route::get('/register', function () {
    return view('normal.register');
});

//ログイン画面(一般)
Route::get('/login', function () {
    return view('normal.login');
});

//メール認証画面(一般)
Route::get('/email', function () {
    return view('normal.email');
});

//出勤登録画面(一般)
Route::get('/attendance', function () {
    return view('normal.attendance_register');
});

//勤怠一覧(一般) 直し
Route::get('/attendance/list', function () {
    return view('normal.attendance_list');
});

//勤怠詳細画面
Route::get('/attendance/id',function () {
    return view('normal.attendance_details');
});

//ログイン画面(管理)
Route::get('/admin/login', function () {
    return view('admin.admin_login');
});

//勤怠一覧(管理) 直し
Route::get('/admin/attendance', function () {
    return view('admin.admin_attendance_list');
});

//勤怠詳細(管理)
Route::get('/admin/attendances/id', function () {
    return view('admin.admin_attendance_details');
});

//スタッフ一覧(管理)
Route::get('/admin/users', function () {
    return view('admin.staff_list');
});

//スタッフ別勤怠一覧(管理)
Route::get('/admin/users/user/attendances',function () {
    return view('admin.staff_attendance_list');
});

//申請一覧(管理)
Route::get('/admin/requests',function () {
    return view('admin.staff_application_list');
});

//修正申請承認画面(管理)
Route::get('/admin/requests/id',function () {
    return view('admin.application_approval');
});
