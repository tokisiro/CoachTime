<?php

namespace App\Http\Middleware;

use App\Providers\RouteServicePorvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
        // 管理者ユーザーの場合
                if ($guard === 'web' && $request->is('admin/*')) {
                // admin/* のパスで、webガードで認証済みの場合
                // webガードで認証されたユーザーがadminパスからログインしたと判断
                    $user = Auth::guard($guard)->user();
dd([
                    'message' => 'RedirectIfAuthenticated is checking user',
                    'guard' => $guard,
                    'user_id' => $user->id ?? null,
                    'user_email' => $user->email ?? null,
                    'user_role_from_db' => $user->role ?? null, // もしUserモデルにroleカラムがあれば
                    'has_role_admin_method_exists' => method_exists($user, 'hasRole'),
                    'user_has_admin_role' => method_exists($user, 'hasRole') ? $user->hasRole('admin') : 'N/A',
                    'request_path' => $request->path(),
                    'request_is_admin_wildcard' => $request->is('admin/*'),
                ]);
                    if ($user && method_exists($user, 'hasRole') && $user->hasRole('admin')) {
                    // UserモデルにhasRoleメソッドがある場合
                        return redirect('/admin/dashboard');
                    }
                    // あるいは、Userモデルのroleカラムを直接参照
                    // if ($user && $user->role === 'admin') {
                    //     return redirect('/admin/dashboard');
                    // }
                }
                // その他の場合（一般ユーザー、または管理者が一般ユーザーのページにログインしようとした場合など）
                return redirect(RouteServiceProvider::HOME); // デフォルトの/dashboardにリダイレクト
    }
    }

        return $next($request);
    }
}