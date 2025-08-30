<?php

use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LoginController;
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


    //ログアウト（一般）
    Route::get('/logout', [LoginController::class, 'destroy']);


//管理者のloginルート
    //ログイン画面表示(管理者)
    Route::get('/admin/login', [LoginController::class, 'showLoginForm'])
    ->middleware('guest:web')
    ->name('admin.login');

    //ログイン認証ルート(管理者)
    Route::prefix('admin')->name('admin.')->group(function () {
    //ログイン画面表示(管理者)
    Route::get('/admin/login', [LoginController::class, 'create'])
        ->middleware('guest:admin')
        ->name('login');

    Route::post('/admin/login', [LoginController::class, 'store'])
        ->middleware('guest:admin');

    Route::post('/admin/logout', [LoginController::class, 'log'])
        ->middleware('auth:admin')
        ->name('logout');
});

// 管理者専用ページ
Route::middleware(['auth:admin', 'can:access-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.admin_attendance_list'); // 管理者ダッシュボードのビュー
    })->name('dashboard');
});

    //ログアウト（管理者）
    Route::get('/admin/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('admin.logout');

//勤怠登録画面
    //出勤登録画面(一般)
    Route::get('/attendance', [AttendanceController::class, 'showAttendanceRegister']);

    //出勤時間登録機能
    Route::post('/record-attendance', [AttendanceController::class, 'recordAttendance'])->middleware('auth');

    //退勤時間登録機能
    Route::post('/record-closing-time', [AttendanceController::class, 'recordClosingTime'])->name('record.closing.time');

    //休憩入時間登録機能
    Route::post('/record-break-in', [AttendanceController::class, 'recordBreakIn'])->name('record.break.in');

    //休憩戻り時間登録機能
    Route::post('/record-break-back', [AttendanceController::class, 'recordBreakBack'])->name('record.break.back');

//勤怠一覧画面関連
    //勤怠一覧画面表示(一般)
    Route::get('/attendance/list', [AttendanceController::class, 'showAttendanceList'])->name('attendances.list');

//勤怠詳細画面関連

    // 勤怠詳細表示
    Route::get('/attendance/{id}', [AttendanceController::class, 'showDetails'])->name('attendance.showDetails')->middleware('auth');

    // 勤怠修正申請送信
    Route::post('/attendance/{id}/apply-edit', [AttendanceController::class, 'applications'])->name('applications');

//申請一覧画面関連(一般)(管理)

    //申請一覧(管理)(一般)
    Route::get('/stamp_correction_request/list',[AttendanceController::class, 'showApplicationsList'])->name('showApplicationsList');


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

//修正申請承認画面(管理)
Route::get('/stamp_correction_request/approve/attendance_correct_request',function () {
    return view('admin.application_approval');
});
