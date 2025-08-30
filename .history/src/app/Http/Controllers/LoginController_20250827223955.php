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
    //ログイン処理(管理者)
    public function store(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }
        //ユーザーをログイン
        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        //requestが/admin/loginから送られているか確認
        if ($request->is('admin/login') && $user->role === 'admin') {
            //送信したのは管理者か確認
            return redirect()->intended('/admin/attendance/list');
        }

        //ログイン失敗時のリダイレクト先
        return redirect()->intended('/admin/login');
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


    
    }
}
