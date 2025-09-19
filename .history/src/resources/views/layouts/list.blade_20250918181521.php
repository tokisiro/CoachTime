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
            <div class="header__inner-navigation" id="mainHeaderNavigation">
                @hasSection('headerNavigation')
                    @yield('headerNavigation')
                @else
                    @include('layouts.header_list')
                @endif
                {{-- または、Blade 11 以降で推奨される @empty --}}
                {{-- @empty('headerNavigation')
                    @include('layouts.header_list')
                @endempty
                @yield('headerNavigation') --}}
            </div>
        </div>
    </header>
    <main>
        @yield('content')
    </main>
    @yield('script')
</body>
</html>

