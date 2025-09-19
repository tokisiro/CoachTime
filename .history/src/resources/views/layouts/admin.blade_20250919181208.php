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
                <a class="header__inner-navigation--attendance" href="{{route('admin.attendances.list')}}">
                    勤怠一覧
                </a>
                <a class="header__inner-navigation--list" href="{{ route('admin.staff.list')}}">
                    スタッフ一覧
                </a>
                <a class="header__inner-navigation--application" href="/request/list">
                    申請一覧
                </a>
                <form id="logout-form-admin" action="/logout" method="POST" style="display: none;">
                @csrf
                    <input type="hidden" name="is_admin_login" value="true"> <!-- 管理者であることを示すパラメータ -->
                </form>
                <a class="header__inner-navigation--logout" href="#" onclick="event.preventDefault(); document.getElementById('logout-form-admin').submit();">
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