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
        //ログインようの
        $this->app->bind(\Laravel\Fortify\Http\Requests\LoginRequest::class, function ($app) {
            return $app->make(\App\Http\Requests\LoginRequest::class);
        });

        $this->app->bind(\Laravel\Fortify\Http\Requests\RegisterRequest::class, function ($app) {
            return $app->make(RegisterRequest::class);
        });

        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::registerView(function () {
            return view('normal.register');
            });

        Fortify::loginView(function () {
            return view('normal.login');
            });


        //ログイン処理(一般)(機能していない)
        Fortify::authenticateUsing(function (Request $request) {

            $loginRequest = new LoginRequest();
            //LoginRequestのrulesを定義
            $rules = $loginRequest->rules();
            //LoginRequestのmessagesを定義
            $messages = $loginRequest->messages();

            //値がルールにあっているかチェック
            $validator = Validator::make($request->all(), $rules, $messages);

            //バリデーションの値をチェック
            if ($validator->fails()) {
                //バリデーションに失敗した時の動作
                throw new ValidationException($validator);
            }

            //バリデーション成功後のログイン処理
            $user = User::where('email', $request->email)->first();

            // ユーザーが存在し、パスワードが一致するかを確認
            if ($user && Hash::check($request->password, $user->password)) {
            return $user; // 認証成功
            }

            // 認証失敗
            return null;
    });

    // ログイン後のリダイレクト先をカスタマイズ
        Fortify::redirects('login', function (Request $request, $user) {

            if ($request->is('admin/login') && $user->role === 'admin') {
                return '/admin/dashboard'; // 管理者用のダッシュボードURL
            }
            return '/dashboard';
    });
    }
}