<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException; // 認証失敗時の例外


class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.login');
    }

    public function login(LoginRequest $request)
    {
        // Fortifyの認証パイプラインをトリガー
        // FortifyServiceProvider::authenticateUsing() がここで呼び出される
        $user = Fortify::authenticate($request);

        if ($user) {
            // 認証成功時にFortifyのLoginResponseを返す
            // これにより FortifyServiceProvider::redirects() が呼び出され、
            // リダイレクト先が決定されます。
            return app(LoginResponse::class);
        }

        // 認証失敗時の処理
        // Fortifyのデフォルトの認証失敗時の振る舞いを模倣
        return redirect()->back()
            ->withInput($request->only('email')) // パスワードは入力させ直す
            ->withErrors([
                'email' => __('auth.failed'), // 'auth.failed' は config/lang/en/auth.php に定義されたエラーメッセージ
            ]);
    }
}
