    <a class="header__inner-navigation--attendance" href="/attendance">
        勤怠
    </a>
    <a class="header__inner-navigation--list" href="/attendance/list">
        勤怠一覧
    </a>
    <a class="header__inner-navigation--application" href="/stamp_correction_request/list">
        申請
    </a>
    <form id="logout-form-user" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
                </form>
                <a class="header__inner-navigation--logout" href="#" onclick="event.preventDefault(); document.getElementById('logout-form-user').submit();">
                    ログアウト
                </a>