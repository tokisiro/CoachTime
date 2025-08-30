<?php

namespace App\Providers;


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
use Laravel\Fortify\Fortify;
use App\Http\Requests\LoginRequest;

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
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::registerView(function () {
            return view('normal.register');
            });

        Fortify::loginView(function () {
            return view('normal.login');
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