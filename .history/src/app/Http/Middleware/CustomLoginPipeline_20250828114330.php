<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Fortify\Fortify;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class CustomLoginPipeline
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

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
        

        if ($user && Hash::check($request->password, $user->password)) {
            // 認証成功
            Auth::login($user, $request->boolean('remember')); // remember me も考慮
            $request->session()->regenerate(); // セッション固定攻撃対策

            // FortifyのauthenticateUsingがユーザーを返すのと同じように、
            // ここではパイプラインの次の処理に進むか、直接レスポンスを返します。
            // 認証成功後、FortifyはFortify::redirects('login', ...) に従います。
            // そのため、ここでは認証が完了したことをFortifyに伝える必要があります。
            // FortifyのAuthenticatedSessionControllerは、loginPipelineからユーザーオブジェクトが返されることを期待します。
            // しかし、パイプラインミドルウェアはResponseを返す必要があります。

            // ここで直接Fortifyのパイプラインに認証済みユーザーを渡すのではなく、
            // 認証を完了させ、Fortifyのコントローラーが通常通りリダイレクトできるようにします。

            // 認証が成功したら、次のミドルウェアに進む
            return $next($request);

        }

        // 認証失敗
        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }
}