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


//一般ユーザーログイン後のリダイレクト先
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('attendance.register'); // または 'dashboard' など、適切なビュー名を指定
    })->name('dashboard'); // ルートに名前を付けておくと後々便利
});

// 管理者用のログイン画面へのGETリクエスト（ログインフォームの表示）
Route::get('/admin/login', function () {
    return view('admin.admin_login'); // FortifyServiceProviderで分岐しているので、これは必須ではないが、明示的に定義するなら
})->name('admin.login')->middleware('guest');

// 管理者ログイン後のリダイレクト先
Route::middleware(['auth', 'can:access-admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('admin.attendances.list');
    })->name('dashboard');
});


//勤怠登録画面
    //出勤登録画面(一般)
    Route::get('/attendance', [AttendanceController::class, 'showAttendanceRegister'])->name('attendance.register');

    //出勤時間登録機能
    Route::post('/record-attendance', [AttendanceController::class, 'recordAttendance'])->middleware('auth');

    //退勤時間登録機能
    Route::post('/record-closing-time', [AttendanceController::class, 'recordClosingTime'])->name('record.closing.time');

    //休憩入時間登録機能
    Route::post('/record-break-in', [AttendanceController::class, 'recordBreakIn'])->name('record.break.in');

    //休憩戻り時間登録機能
    Route::post('/record-break-back', [AttendanceController::class, 'recordBreakBack'])->name('record.break.back');

//勤怠詳細画面関連

    Route::middleware(['auth:web', 'auth:admin'])->group(function () {
    // ... 他の認証が必要なルート ...
    Route::get('/attendance/{id}', [AttendanceController::class, 'showDetails'])->name('attendance.showDetails');
    });

    // 勤怠修正申請送信
    Route::post('/attendance/{id}/apply-edit', [AttendanceController::class, 'applications'])->name('applications');

//勤怠一覧画面関連
    //勤怠一覧画面表示(一般)
    Route::get('/attendance/list', [AttendanceController::class, 'showAttendanceList'])->name('attendances.list');



//申請一覧画面関連(一般)(管理)

    //申請一覧(管理)(一般)
    Route::get('/stamp_correction_request/list',[AttendanceController::class, 'showApplicationsList'])->name('showApplicationsList');

//勤務一覧画面関連(管理)

    //勤怠一覧(管理)
    Route::get('/admin/attendance/list', [AttendanceController::class, 'showAdminAttendanceList'])->name('admin.attendances.list');

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
