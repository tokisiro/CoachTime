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
    //ログイン処理(一般)
    public function store(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }
        Auth::login($user, $request->boolean('remember'));

        // セッション再生成 (セキュリティ対策)
        $request->session()->regenerate();

        // ログイン元のURLが '/admin/login' であるか、またはユーザーが管理者ロールかを確認
        if ($request->is('admin/login') && $user->role === 'admin') {
            return redirect()->intended('/admin/attendance/list');
        }

        // それ以外（一般ユーザーのログイン、または管理者以外のロールで管理者ログインページからログインした場合など）
        // RouteServiceProvider::HOME または特定のパスにリダイレクト
        // 例: RouteServiceProvider::HOME に設定したパスへ
        return redirect()->intended(route('attendance.home') ?? '/attendance'); // 例: /attendance に 'attendance.home' という名前のルートがある場合
        // return redirect()->intended('/attendance'); // または直接パスを指定
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
    public function login(LoginRequest $request)
    {
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
