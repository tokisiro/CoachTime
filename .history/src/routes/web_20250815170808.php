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

// 管理者向けのルート
Route::prefix('admin')->name('admin.')->group(function () {
    // ログインフォーム表示
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');
    // ログイン処理
    Route::post('/login', [AdminLoginController::class, 'login']);

    // 管理者用のダッシュボードなど（認証ガードを適用）
    Route::middleware('auth:web')->group(function () { // ここも 'web' ガード
        Route::get('/admin/login', function () {
            // 管理者でない場合はアクセスさせない
            if (Auth::user()->role !== 'admin') {
                Auth::guard('web')->logout(); // ログアウトさせる
                return redirect('/admin/login')->withErrors(['error' => '管理者としてログインしてください。']);
            }
            return "Admin Dashboard - Welcome, Admin " . Auth::user()->name;
        })->name('dashboard');
    });
});

// 一般ユーザー向けのダッシュボードなど
Route::middleware('auth:web')->group(function () {
    Route::get('/login', function () {
        // 一般ユーザーでない場合はアクセスさせない（管理者もアクセス可能にする場合はこのチェックは不要）
        if (Auth::user()->role !== 'user') {
            // 例えば管理者であれば admin.dashboard へリダイレクト
            return redirect()->route('admin.login'); // 管理者なら管理者ダッシュボードへリダイレクト
        }
        return "User Dashboard - Welcome, " . Auth::user()->name;
    })->name('dashboard');
});

// ログアウトルート（Fortifyのデフォルトに任せるか、独自に定義）
// Fortify::routes() が /logout ルートも登録します。
// 必要であれば、個別にログアウトルートを定義し、ログアウト後に適切な場所にリダイレクトさせます。
Route::post('/logout', function (Request $request) {
    Auth::guard('web')->logout(); // 'web' ガードでログアウト

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login'); // ログアウト後のリダイレクト先
})->name('logout');

// 管理者ログアウト
Route::post('/admin/logout', function (Request $request) {
    Auth::guard('web')->logout(); // 同じwebガードでログアウト

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/admin/login'); // 管理者ログアウト後のリダイレクト先
})->name('admin.logout');


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
