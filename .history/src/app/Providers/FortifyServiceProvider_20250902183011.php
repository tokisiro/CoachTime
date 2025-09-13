<?php

namespace App\Providers;

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


        //Fortify::redirects('login', function (Request $request, $user) {

        //dd('Fortify redirects called!', ['user_id' => $user->id ?? 'N/A', // $userがnullでないことを確認
            'user_role' => $user->role ?? 'N/A',
             'current_guard_name' => Auth::guard()->getName(), // 認証されたガードの名前
            'request_is_admin_path' => $request->is('admin/*'),
            'intended_url' => $request->session()->pull('url.intended'),
        ]);

        // Auth::guard('admin')->check() は、adminガードで認証されているかを確認します。
        // $user->role === 'admin' は、データベース上のロールを確認します。
        if (Auth::guard('admin')->check() && $user->role === 'admin') {
            //return '/admin/dashboard'; // 管理者用のダッシュボードURL
        }

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