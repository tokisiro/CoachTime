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
    
}
