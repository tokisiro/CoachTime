<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/layouts/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layouts/admin.css') }}">
    @yield('css')
</head>
<body>
    <header class="header">
        <div class="header__inner">
            <div class="header__inner-logo">
                <img class="header__inner-logo--item" src="/images/logo.svg" alt="ロゴ">
            </div>
            <div class="header__inner-navigation">
                <a class="header__inner-navigation--attendance" href="/admin/attendance">
                    勤怠一覧
                </a>
                <a class="header__inner-navigation--list" href="/admin/staff/list">
                    スタッフ一覧
                </a>
                <a class="header__inner-navigation--application" href="/stamp_correction_request/list">
                    申請一覧
                </a>
                <a class="header__inner-navigation--logout" href="">
                    ログアウト
                </a>
            </div>
        </div>
    </header>
    <main>
        @yield('content')
    </main>
</body>
</html>