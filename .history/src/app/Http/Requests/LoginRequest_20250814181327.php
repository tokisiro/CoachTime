<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\RateLimiter; // ログイン試行回数制限用
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException; // 認証失敗時の例外
use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Auth\Events\Lockout;

class LoginRequest extends FormRequest
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
            'email.required' => 'メールアドレスは必須です。',
            'email.email' => '有効なメールアドレス形式で入力してください。',
            'email.string' => 'メールアドレスは必須です。',
            'password.required' => 'パスワードは必須です。',
            'password.confirmed' => 'パスワードと確認用パスワードが一致しません。',
            // 必要に応じて他のメッセージを追加
        ];
    }

    //public function authenticate(): void{
        //$this->ensureIsNotRateLimited(); // ログイン試行回数制限のチェック

        //if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            //RateLimiter::hit($this->throttleKey()); // 認証失敗時に試行回数をカウント

            //throw ValidationException::withMessages([
            //    'email' => trans('auth.failed'),
            //]);
        }

        //RateLimiter::clear($this->throttleKey()); // 認証成功時に試行回数をクリア
    //}

    //public function ensureIsNotRateLimited(): void
    {
        //if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) { // 5回以上失敗したら制限
            return;
        }

        //event(new Lockout($this));

        //$seconds = RateLimiter::availableIn($this->throttleKey());

        //throw ValidationException::withMessages([
            //'email' => trans('auth.throttle', [
            //    'seconds' => $seconds,
            //    'minutes' => ceil($seconds / 60),
            //]),
        //]);
    //}


    //public function throttleKey(): string
    {
        //return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    }
//}
