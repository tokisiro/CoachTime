@component('mail::message')
# メールアドレスの認証

{{ $name }}様

○○システムへのご登録ありがとうございます。

お手数ですが、以下のボタンをクリックしてメールアドレスの確認を完了してください。

@component('mail::button', ['url' => $url])
メールアドレスを確認する
@endcomponent

このメールに心当たりがない場合は、このまま破棄してください。

<small>このリンクは {{ config('auth.verification.expire', 60) }} 分間有効です。</small>

よろしくお願いいたします。
○○システム運営事務局

@endcomponent