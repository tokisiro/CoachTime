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
    //ログイン処理()
    public function store(LoginRequest $request)
    {
        return $this->loginPipeline($request)->then(function ($request) {
            return app(LoginResponse::class);
        });
    }

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
    public function showLoginForm()
    {
        return view('admin.admin_login');
    }

    //ログイン処理(管理者)
    
}
