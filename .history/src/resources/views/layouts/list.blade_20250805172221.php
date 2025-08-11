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
                <img class="header__inner-logo--item" src="images/logo.svg" alt="ロゴ">
                <div class="header__inner-nave">
                <a href="">
                    勤怠
                </a>
                <a href="">
                    勤怠一覧
                </a>
                <a href="">
                    申請
                </a>
                <a href="">
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