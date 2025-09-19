@component('mail::message')
# メールアドレスの認証

※このメールは、【CoachTime】にご登録いただいたメールアドレス宛に自動的に送信しています。

{{ $name }}様

この度は、【CoachTime】の会員登録にお申込みいただきまして誠にありがとうございます。

現在、仮登録の状態です。

以下のリンクをクリックしてメールアドレスの認証を完了してください。

@component('mail::button', ['url' => $url])
メールアドレスを認証する
@endcomponent


【ご注意】

本メールに身に覚えの無い場合は、本メールを破棄していただきますようお願いいたします。

<small>このリンクは {{ config('auth.verification.expire', 60) }} 分間有効です。</small>

＊＊＊お問い合わせ先＊＊＊

住所：

〇〇株式会社 サポートセンター

TEL：

*お急ぎの方は上の電話番号に連絡ください。

@endcomponent