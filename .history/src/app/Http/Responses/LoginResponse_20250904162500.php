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


        // まず、admin ガードでログインしているかチェック
        if (Auth::guard('admin')->check()) {
            return $request->wantsJson()
                        ? new JsonResponse(['url' => route('admin.dashboard')], 200) // route() を使うことを推奨
                        : redirect()->intended(route('admin.dashboard')); // route() を使うことを推奨
        }

        // 次に、web ガードでログインしているかチェック（一般ユーザー）
        if (Auth::guard('web')->check()) {
            return $request->wantsJson()
                        ? new JsonResponse(['url' => route('dashboard')], 200) // route() を使うことを推奨
                        : redirect()->intended(route('dashboard')); // route() を使うことを推奨
        }

        // 一般ユーザーの場合は /dashboard にリダイレクト
        return $request->wantsJson()
                    ? new JsonResponse(['url' => config('fortify.home')], 200)
                    : redirect()->intended(config('fortify.home'));
    }
}