@extends('layouts.normal')

@section('css')
<link rel="stylesheet" href="{{ asset('css/normalregister.css') }}" />
@endsection

@section('content')
<div class="register">
        @if(session('status'))
        <div class="register-message">
            {{ session('status') }}
        </div>
        @endif
        <div class="register-title">
            <h2 class="register-title__item">
                会員登録
            </h2>
        </div>
        <form class="register-form" action="" method="post" novalidate="novalidate">
        @csrf
            <div class="register-form__name">
                <label class="register-form__name-label">
                    ユーザー名
                </label>
                <input class="register-form__name-item" type="text" name="name" value="{{ old('name') }}">
                <div class="register-form__name-error">
                    @error('name')
                        {{$message}}
                    @enderror
                </div>
            </div>
            <div class="register-form__email">
                <label class="register-form__email-label">
                    メールアドレス
                </label>
                <input class="register-form__email-item" type="email" name="email" value="{{ old('email') }}">
                <div class="register-form__email-error">
                    @error('email')
                        {{$message}}
                    @enderror
                </div>
            </div>
            <div class="register-form__password">
                <label class="register-form__password-label">
                    パスワード
                </label>
                <input class="register-form__password-item" type="password" name="password">
                <div class="register-form__password-error">
                    @error('password')
                        {{$message}}
                    @enderror
                </div>
            </div>
            <div class="register-form__confirmation">
                <label class="register-form__confirmation-label">
                    確認用パスワード
                </label>
                <input class="register-form__confirmation-item" type="password" name="password_confirmation">
                <div class="register-form__confirmation-error">
                    @error('password_confirmation')
                        {{$message}}
                    @enderror
                </div>
            </div>
            <div class="register-form__button">
                <button class="register-form__button-item">
                    登録する
                </button>
            </div>
        </form>
        <div class="register-link">
            <a class="register-link__login" href="/login">
                ログインはこちら
            </a>
        </div>
    </div>
@endsection