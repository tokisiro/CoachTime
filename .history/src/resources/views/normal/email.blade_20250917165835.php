@extends('layouts.normal')

@section('css')
<link rel="stylesheet" href="{{ asset('css/normal/email.css') }}" />
@endsection

@section('content')
    <div class="email">
        <div class="email-function">
            <div class="email-function__message">
                <p class="email-function__message-item">
                    登録していただいたメールアドレスに認証メールを送付しました。<br>
                    メール認証を完了してください。
                </p>
            </div>
            <div class="email-function__certification">
                <a class="email-function__certification-item" href="http://localhost:8025/">認証はこちらから</a>
            </div>
            <form class="email-function__resend" method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button class="email-function__resend-item" type="submit">認証メールを再送する
                </button>
            </form>
        </div>
    </div>
    @endsection

