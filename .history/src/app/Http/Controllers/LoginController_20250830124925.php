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
use App\Providers\RouteServiceProvider;


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

        // 認証を試みる
        if (! Auth::guard('admin')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // 認証成功後、ユーザーのroleを確認
        $user = Auth::guard('admin')->user();
        if ($user->role !== 'admin') { // roleが'admin'ではない場合はログイン失敗
            Auth::guard('admin')->logout(); // 念のためログアウト
            
        }

        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::ADMIN_HOME);
    }


    public function leave(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/admin/login'); // ログアウト後のリダイレクト先
    }
}
