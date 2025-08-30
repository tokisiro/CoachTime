<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest; // 作成するLoginRequestをインポート
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException; // 認証失敗時の例外


class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login'); // ログインフォームのビュー名を指定
    }
}
