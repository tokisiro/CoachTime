<?php

namespace App\Providers;

use App\Http\Responses\LoginResponse;
use Illuminate\Support\Facades\Auth;
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
        $this->app->singleton(
            \Laravel\Fortify\Contracts\LoginResponse::class,
            \App\Http\Responses\LoginResponse::class
            // カスタムのLoginResponseに置き換える
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::registerView(function () {
            return view('normal.register');
            });

        Fortify::loginView(function () {
            if (request()->is('admin/login')) {
                return view('admin.admin_login'); // 管理者用のログインビューのパス
            }
            // それ以外は一般ユーザー用のログインビューを表示 (Fortifyのデフォルト /login)
            return view('normal.login'); // 一般ユーザー用のログインビューのパス
        });

        Fortify::authenticateUsing(function (Request $request) {
            $email = $request->email;
            $password = $request->password;
            $remember = $request->boolean('remember');

            if ($request->has('is_admin_login')) { // '/admin/login' からの認証リクエスト
                if (Auth::guard('admin')->attempt(['email' => $email, 'password' => $password], $remember)) {
                    //return Auth::guard('admin')->user();
                }
            } else { // 通常のログイン (web ガード)
                if (Auth::guard('web')->attempt(['email' => $email, 'password' => $password], $remember)) {
                    //return Auth::guard('web')->user();
                }
            }

            //return null; // 認証失敗
        //});
    }
}