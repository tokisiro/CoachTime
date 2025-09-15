<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Auth\Events\Lockout;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;

class LoginRequest extends FortifyLoginRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'メールアドレスを入力してください。',
            'email.email' => 'メールアドレス形式で入力してください。',
            'email.string' => '',
            'password.required' => 'パスワードを入力してください。',
            'password.string' => '',
            // 必要に応じて他のメッセージを追加
        ];
    }

    //public function authenticate(): void
    //{
        //$this->ensureIsNotRateLimited();

        // ログインを試みるガードを動的に決定
        // リクエストのパスが 'admin/*' であれば 'admin' ガード、そうでなければ 'web' ガード
        //$guard_name = $this->is('admin/*') ? 'admin' : 'web';

        //if (! Auth::guard($guard_name)->attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            //RateLimiter::hit($this->throttleKey());

            //throw ValidationException::withMessages([
                //'email' => trans('auth.failed'),
            //]);
        }

        // 認証されたユーザーの 'role' が 'admin' でないのに 'admin' ガードでログインしようとした場合、ログアウトさせる
        $user = Auth::guard($guard_name)->user();
        if ($guard_name === 'admin' && ($user && $user->role !== 'admin')) {
            Auth::guard($guard_name)->logout();
            throw ValidationException::withMessages([
                'email' => '管理者権限がありません。', // より具体的なメッセージ
            ]);
        }


        RateLimiter::clear($this->throttleKey());
    //}

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    }

}