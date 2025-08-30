<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest; // 作成するRegisterRequestをインポート
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class RegisterController extends Controller
{
    public function create()
    {
        return view('nomale.register'); // 登録フォームのビュー名を指定
    }

    public function store(RegisterRequest $request, CreatesNewUsers $creator): RedirectResponse
    {
        // Form Request によってバリデーション済み
        // $creator (CreateNewUser) を使ってユーザーを作成
        event(new Registered($user = $creator->create($request->all())));

        Auth::login($user);

        return redirect(route('addtendance')); // 登録後のリダイレクト先
    }
}
