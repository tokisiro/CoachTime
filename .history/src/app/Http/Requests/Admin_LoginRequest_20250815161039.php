<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Admin_LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
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

    public function authenticate(): void{
        $this->ensureIsNotRateLimited(); // ログイン試行回数制限のチェック

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey()); // 認証失敗時に試行回数をカウント

            throw ValidationException::withMessages([
                'email' => 'ログイン情報が登録されていません。',
            ]);
        }

        RateLimiter::clear($this->throttleKey()); // 認証成功時に試行回数をクリア
    }

    //public function ensureIsNotRateLimited(): void
    //{
        //if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) { // 5回以上失敗したら制限
            //return;
        //}

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
    //{
        //return Str::transliterate(Str::lower($this->input('email')).'|'.$this->ip());
    //}

}
