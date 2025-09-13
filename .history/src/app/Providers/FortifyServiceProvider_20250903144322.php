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
            \Laravel\Fortify\Contracts\LoginResponse::class, // これを
            \App\Http\Responses\LoginResponse::class // カスタムのLoginResponseに置き換える
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        //Fortify::authenticateUsing(function (Request $request) {

            //$user = null;

            //if ($request->is('admin/*')) {
                //if (Auth::guard('admin')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            //$user = Auth::guard('admin')->user(); // 認証成功したらユーザーを取得
        //}
    //} else {
        //if (Auth::guard('web')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            //$user = Auth::guard('web')->user(); // 認証成功したらユーザーを取得
            //}
        //}

    //return $user; // ユーザーオブジェクト (認証成功) または null (認証失敗) を返す
    //});

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


    /Fortify::redirects('login', function (Request $request, $user) {

        // Auth::guard('admin')->check() は、adminガードで認証されているかを確認します。
        // $user->role === 'admin' は、データベース上のロールを確認します。
        //if ($user && method_exists($user, 'hasRole') && $user->hasRole('admin')) {
        //return '/admin/dashboard';
    //}

        // 一般ユーザーの場合、または管理者ではない場合
        //return '/dashboard'; // 一般ユーザー用のダッシュボードURL
    //});

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