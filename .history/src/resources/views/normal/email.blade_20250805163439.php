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
                <a class="email-function__certification-item" href="{{ $verificationUrl }}">認証はこちらから</a>
            </div>
            <div class="email-function__resend">
                <a class="email-function__resend-item" href="{{ route('resendVerification', ['id' => $user->id]) }}">認証メールを再送する</a>
                </a>
            </div>
        </div>
    </div>
    @endsection

