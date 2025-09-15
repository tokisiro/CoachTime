<?php

use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Route;
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

// 管理者用のログインフォーム表示（Fortifyで管理されているなら不要だが、明示するなら）
Route::get('/admin/login', function () {
    return view('admin.admin_login');
})->name('admin.login')->middleware('guest:admin'); // guest:admin で管理者として認証されていない場合のみアクセス可能


// --- 一般ユーザー（web ガード）向けのルートグループ ---
Route::middleware('auth:web')->group(function () {
    // ログイン後のリダイレクト先（FortifyのHOME設定と連動）
    Route::get('/dashboard', function () {
        return redirect()->route('attendance.register');
    })->name('dashboard');

    // 勤怠登録画面
    Route::get('/attendance', [AttendanceController::class, 'showAttendanceRegister'])->name('attendance.register');

    // 勤怠登録機能（出勤）
    Route::post('/record-attendance', [AttendanceController::class, 'recordAttendance'])->name('record.attendance');

    // 退勤時間登録機能
    Route::post('/record-closing-time', [AttendanceController::class, 'recordClosingTime'])->name('record.closing.time');

    // 休憩入時間登録機能
    Route::post('/record-break-in', [AttendanceController::class, 'recordBreakIn'])->name('record.break.in');

    // 休憩戻り時間登録機能
    Route::post('/record-break-back', [AttendanceController::class, 'recordBreakBack'])->name('record.break.back');

    // 勤怠一覧画面表示(一般ユーザー)
    Route::get('/attendance/list', [AttendanceController::class, 'showAttendanceList'])->name('attendances.list');
});


// --- 管理者ユーザー（admin ガード）向けのルートグループ ---
Route::middleware(['auth:admin', 'can:access-admin'])->prefix('admin')->name('admin.')->group(function () {
    // ログイン後のリダイレクト先（Fortifyのadminガード設定と連動）
    Route::get('/dashboard', function () {
        return redirect()->route('admin.attendances.list');
    })->name('dashboard');

    // 勤怠一覧(管理)
    Route::get('/attendance/list', [AttendanceController::class, 'showAdminAttendanceList'])->name('attendances.list');

    // スタッフ一覧(管理)
    Route::get('/staff/list',
        [AttendanceController::class, 'showStaffList'])->name('staff.list');

    // スタッフ別勤怠一覧(管理)
    Route::get('/attendances/staff/{id}',[AttendanceController::class, 'showStaffAttendance'])->name('attendances.staff.list');

    
    Route::get('/attendance/{id}/csv/{year}/{month}', [AttendanceController::class, 'exportCsv'])->name('attendance.exportCsv');

    //修正申請承認機能(管理)
    Route::post('/attendance/{id}/approve', [AttendanceController::class, 'approve'])->name('approve');
});



Route::middleware(['auth:web,admin'])->group(function () {
    // 勤怠詳細画面表示(管理)(一般)
    Route::get('/attendance/{id}', [AttendanceController::class, 'showDetails'])->name('attendance.showDetails');

    // 勤怠修正申請送信
    Route::post('/attendance/{id}/apply-edit', [AttendanceController::class, 'applications'])->name('applications');

    // 申請一覧(管理)(一般)
    Route::get('/stamp_correction_request/list',[AttendanceController::class, 'showApplicationsList'])->name('showApplicationsList');
});

//ログアウト機能
Route::post('/logout', [LoginController::class,'logout'])->name('logout');