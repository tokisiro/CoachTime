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
    public function create()
    {
        return view('normal.login'); // ログインフォームのビュー名を指定
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        // Form Request によってバリデーション済み
        // 認証情報を取得
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard')); // ログイン後のリダイレクト先
        }

        // 認証失敗
        throw ValidationException::withMessages([
            'email' => trans('auth.failed'), // 認証失敗時のメッセージ (lang/ja/auth.phpを参照)
        ]);
    }
}
