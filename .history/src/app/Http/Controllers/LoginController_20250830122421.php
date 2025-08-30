<?php

namespace App\Http\Controllers;

use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\LoginResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException; // 認証失敗時の例外


class LoginController extends Controller
{

    //ログアウト機能(一般)
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // ログアウト後のリダイレクト先
        return redirect('/login');
    }


    //ログイン画面表示(管理者)
    public function create()
    {
        return view('admin.admin_login');
    }

    //ログイン処理(管理者)
    public function store(Request $request)
    {

        // is_adminがtrueのユーザーのみ認証
        if (! Auth::guard('admin')->attempt($request->only('email', 'password'), $request->boolean('remember')) ||
            ! Auth::guard('admin')->user()->is_admin) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::ADMIN_HOME); // 管理者ログイン後のリダイレクト先
    }

    public function leave(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/admin/login'); // ログアウト後のリダイレクト先
    }
}
