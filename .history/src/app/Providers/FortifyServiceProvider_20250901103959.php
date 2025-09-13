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
        $this->app->bind(LoginRequest::class, function ($app) {
            if ($app->make(Request::class)->is('admin/*')) {
            return $app->make(\App\Http\Requests\LoginRequest::class);
            }
            // 一般ユーザーログインの場合、通常のLoginRequestを使う
            return $app->make(\App\Http\Requests\LoginRequest::class);
        });

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

        // Fortifyが使用するガードを動的に決定
        // これはconfig/fortify.phpの'guards'配列を上書きする
        Fortify::guard(function (Request $request) {
            // リクエストのパスで管理者ログインかどうかを判断
            if ($request->is('admin/*')) {
                return 'admin'; // 管理者ログインの場合は 'admin' ガードを使用
            }
            return 'web'; // それ以外は 'web' ガードを使用
        });

        // ログイン後のリダイレクト先をカスタマイズ
        Fortify::redirects('login', function (Request $request, $user) {
            // ユーザーのroleとリクエストのパスに基づいてリダイレクト先を決定
            if ($user->role === 'admin' && $request->is('admin/*')) {
                return '/admin/dashboard'; // 管理者用のダッシュボードURL
            }
            return '/dashboard'; // 一般ユーザー用のダッシュボードURL
        });

        //ログアウト後のリダイレクト先をカスタマイズ (オプション)
        Fortify::redirects('logout', function (Request $request) {
            if ($request->is('admin/*')) {
        //         return '/admin/login'; // 管理者ログアウト後のリダイレクト先
        //     }
        //     return '/login'; // 一般ユーザーログアウト後のリダイレクト先
        // });
    }
}