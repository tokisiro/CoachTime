<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Fortify;
use App\Http\Controllers\RegisterController;
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


Route::middleware('guest')->group(function () {
    //ログイン画面()
    Route::get('/login', [LoginController::class, 'create'])
                ->name('login');

    Route::post('/login', [LoginController::class, 'store']);
});

//ログアウト機能
Route::post('/logout', [LoginController::class, 'destroy'])
            ->middleware('auth')->name('logout');

Route::middleware('guest')->group(function () {
    //会員登録画面
    Route::get('/register', [RegisterController::class, 'create'])
                ->name('register');
    //会員登録機能
    Route::post('/register', [RegisterController::class, 'store']);
});

//出勤登録画面(一般)
Route::get('/attendance', function () {
    return view('normal.attendance_register');
});

//勤怠一覧(一般)
Route::get('/attendance/list', function () {
    return view('normal.attendance_list');
});

//勤怠詳細画面(一般)(管理)
Route::get('/attendance/id',function () {
    return view('normal.attendance_details');
});

//ログイン画面(管理)
Route::get('/admin/login', function () {
    return view('admin.admin_login');
});

//勤怠一覧(管理)
Route::get('/admin/attendance', function () {
    return view('admin.admin_attendance_list');
});

//スタッフ一覧(管理)
Route::get('/admin/staff/list', function () {
    return view('admin.staff_list');
});

//スタッフ別勤怠一覧(管理)
Route::get('/admin/attendances/staff/id',function () {
    return view('admin.staff_attendance_list');
});

//申請一覧(管理)(一般)
Route::get('/stamp_correction_request/list',function () {
    return view('admin.staff_application_list');
});

//修正申請承認画面(管理)
Route::get('/stamp_correction_request/approve/attendance_correct_request',function () {
    return view('admin.application_approval');
});
