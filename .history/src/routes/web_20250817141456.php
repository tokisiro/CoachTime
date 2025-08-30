<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Fortify;
use App\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController; // ログイン・ログアウトのアクション
use App\Http\Requests\Auth\LoginRequest; // 一般ユーザー用のLoginRequest
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
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



    //ログイン後に表示する画面(一般)
    Route::middleware('auth:web')->group(function () {
        Route::get('/dashboard', function () {
            if (Auth::user()->role === 'admin') {
                return redirect()->route('dashboard');
            }
            return "User Dashboard - Welcome, " . Auth::user()->name;
        })->name('dashboard');
    });


//管理者のloginルート
    //ログイン画面表示(管理者)
    Route::get('/admin/login', [LoginController::class, 'showLoginForm'])
    ->middleware('guest:web')
    ->name('admin.login');

    //ログイン機能(管理者)
    Route::post('/admin/login', [LoginController::class, 'login'])
    ->middleware('guest:web');

    Route::prefix('admin')->name('admin.')->middleware('auth:web')->group(function () {
    Route::get('/dashboard', function () {
        if (Auth::user()->role !== 'admin') {
            Auth::guard('web')->logout();
            return redirect()->route('admin.login')->withErrors(['error' => '管理者としてログインしてください。']);
        }
        return "Admin Dashboard - Welcome, Admin " . Auth::user()->name;
    })->name('dashboard');
    });

    //ログアウト（管理者）
    Route::get('/admin/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('admin.logout');


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
