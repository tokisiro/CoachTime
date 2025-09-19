@component('mail::message')
# メールアドレスの認証

※このメールは、【CoachTime】にご登録いただいたメールアドレス宛に自動的に送信しています。

{{ $name }}様

この度は、{】の会員登録にお申込みいただきまして誠にありがとうございます。

現在、仮登録の状態です。

以下のリンクをクリックしてメールアドレスの認証を完了してください。

@component('mail::button', ['url' => $url])
メールアドレスを確認する
@endcomponent

このメールに心当たりがない場合は、このまま破棄してください。

<small>このリンクは {{ config('auth.verification.expire', 60) }} 分間有効です。</small>

よろしくお願いいたします。
○○システム運営事務局

@endcomponent