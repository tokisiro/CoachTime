<?php

namespace App\Providers;


use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\Actions\Fortify\CreateNewUser;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //ログイン用のバリデーション
        //カスタムrequestを優先するよう記述
        $this->app->bind(LoginRequest::class, CustomLoginRequest::class);

        //会員登録用のバリデーション
        //カスタムrequestを優先するよう記述
        $this->app->bind(\Laravel\Fortify\Http\Requests\RegisterRequest::class, function ($app) {
            return $app->make(\App\Http\Requests\RegisterRequest::class); // useしたRegisterRequestクラスを使う
        });

        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::registerView(function () {
            return view('normal.register');
            });

        Fortify::loginView(function () {
            return view('normal.login');
            });


        // ログイン後のリダイレクト先をカスタマイズ
        Fortify::redirects('login', function (Request $request, $user) {
            dd([
        'user_role' => $user->role,
        'request_is_admin' => $request->is('admin/*'),
        'intended_url' => $request->session()->pull('url.intended'), // もしセッションにリダイレクト先の情報があれば
    ]);
            // ユーザーのroleとリクエストのパスに基づいてリダイレクト先を決定
            if ($user && $user->role === 'admin' && Auth::guard('admin')->check()) {
                return '/admin/dashboard'; // 管理者用のダッシュボードURL
            }
            return '/dashboard'; // 一般ユーザー用のダッシュボードURL
        });

        //ログアウト後のリダイレクト先をカスタマイズ (オプション)
        Fortify::redirects('logout', function (Request $request) {
            if ($request->is('admin/*')) {
                return '/admin/login';
                 // 管理者ログアウト後のリダイレクト先
            }
            return '/login';
            // 一般ユーザーログアウト後のリダイレクト先
        });
    }
}