<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request): Response
    {
        $user = Auth::user(); // 現在認証されたユーザーを取得

        // ここにddを仕込む
        dd([
            'message' => 'Custom LoginResponse executed!',
            'user_id' => $user->id ?? null,
            'user_email' => $user->email ?? null,
            'user_role_from_db' => $user->role ?? null, // Userモデルにroleカラムがあれば
            'has_role_admin_method_exists' => method_exists($user, 'hasRole'),
            'user_has_admin_role' => method_exists($user, 'hasRole') ? $user->hasRole('admin') : 'N/A',
            'is_admin_login_path' => $request->is('admin/login'), // 管理者ログインURLからのリクエストか
        ]);


        if ($user && method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            // 管理者の場合は /admin/dashboard にリダイレクト
            return $request->wantsJson()
                        ? new JsonResponse(['url' => '/admin/dashboard'], 200)
                        : redirect()->intended('/admin/dashboard');
        }

        // 一般ユーザーの場合は /dashboard にリダイレクト
        return $request->wantsJson()
                    ? new JsonResponse(['url' => config('fortify.home')], 200)
                    : redirect()->intended(config('fortify.home'));
    }
}