@extends('layouts.normal')

@section('css')
<link rel="stylesheet" href="{{ asset('css/normal/email.css') }}" />
@endsection

@section('content')
    <div class="email">
        @if(session('status'))
        <div>
            <p class="email-Resend">
                {{ session('status') }}
            </p>
        </div>
        @endif
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
            < class="email-function__resend">
                <button class="email-function__resend-item" href="">認証メールを再送する
                </button>
            </>
        </div>
    </div>
    @endsection

