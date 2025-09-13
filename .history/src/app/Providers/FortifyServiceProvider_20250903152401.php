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
            if (request()->is('admin/login')) {
                return view('admin.admin_login'); // 管理者用のログインビューのパス
            }
            // それ以外は一般ユーザー用のログインビューを表示 (Fortifyのデフォルト /login)
            return view('normal.login'); // 一般ユーザー用のログインビューのパス
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