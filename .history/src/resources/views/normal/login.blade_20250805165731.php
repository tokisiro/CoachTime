@extends('layouts.normal')

@section('css')
<link rel="stylesheet" href="{{ asset('css/nomal/login.css') }}" />
@endsection

@section('content')
<div class="login">
        <div class="login-title">
            <h2 class="login-title__item">
                ログイン
            </h2>
        </div>
        <form class="login-form" action="/login" method="post">
        @csrf
            <div class="login-form__email">
                <label class="login-form__email-label">
                    メールアドレス
                </label>
                <input class="login-form__email-item" type="email" name="email" value="{{ old('email') }}">
                <div class="login-form__email-error">
                    @error('email')
                        {{$message}}
                    @enderror
                </div>
            </div>
            <div class="login-form__password">
                <label class="login-form__password-label">
                    パスワード
                </label>
                <input class="login-form__password-item" type="password" name="password">
                <div class="login-form__password-error">
                    @error('password')
                        {{$message}}
                    @enderror
                </div>
            </div>
            <div class="login-form__button">
                <button class="login-form__button-item">
                    ログインする
                </button>
            </div>
        </form>
        <div class="login-link">
            <a class="login-link__button" href="/register">
                会員登録はこちら
            </a>
        </div>
@endsection