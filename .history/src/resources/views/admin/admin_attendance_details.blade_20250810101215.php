@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/admin_attendance_details.css') }}" />
@endsection

<!勤怠詳細画面(管理者)>
@section('content')
<div class="attendance-list">
    <h2 class="attendance-list__title">
        勤怠詳細
    </h2>
    <form action="">
        <table class="attendance-list__table">
            <tr>
                <th>名前</th><td></td>
        </tr>
        <tr>
            <th>日付</th><td></td>
        </tr>
        <tr>
            <th>出勤・退勤</th>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <th>休憩</th>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <th>休憩２</th>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <th>備考</th>
            <td></td>
        </tr>
    </table>
    <button>
        修正
    </button>
    </form>
    </div>
</div>
@endsection