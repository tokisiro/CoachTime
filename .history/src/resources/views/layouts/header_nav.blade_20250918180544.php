    <a class="header__inner-navigation--attendance" href="/attendance/list">
        今月の出勤一覧
    </a>
    <a class="header__inner-navigation--list" href="/stamp_correction_request/list">
        申請一覧
    </a>
    <form id="logout-form-user" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
    </form>
    <a class="header__inner-navigation--logout" href="#" onclick="event.preventDefault(); document.getElementById('logout-form-user').submit();">
        ログアウト
    </a>