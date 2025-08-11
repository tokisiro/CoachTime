<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/layouts/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layouts/list.css') }}">
    @yield('css')
</head>
<body>
    <header class="header">
        <div class="header__inner">
            <div class="header__inner-logo">
                <img class="header__inner-logo--item" src="/images/logo.svg" alt="ロゴ">
            </div>
            <div class="header__inner-navigation">
                <a class="header__inner-navigation--attendance" href="/attendance">
                    勤怠
                </a>
                <a class="header__inner-navigation--list" href="/attendance/list">
                    勤怠一覧
                </a>
                <a class="header__inner-navigation--application" href="">
                    申請
                </a>
                <a class="header__inner-navigation--logout" href="/logout">
                    ログアウト
                </a>
            </div>
        </div>
    </header>
    <main>
        @yield('content')
    </main>
    @yield('script')
</body>
</html>