@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff_attendance_list.css') }}" />
@endsection

<!スタッフ別勤怠一覧(管理者)>
@section('content')
<div class="attendance-list">
    <h2 class="attendance-list__title">
        ○年○月○日の勤怠
    </h2>
    <div class="attendance-list__date">
        <p>
            <-前日
        </p>
        <p>
            日付
        </p>
        <p>
            翌日->
        </p>
    </div>
    <table class="attendance-list__table">
        <tr>
            <th>名前</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><a class="attendance-list__table-detail" href="/admin/attendances/{id}">詳細</a></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><a class="attendance-list__table-detail" href="/admin/attendances/{id}">詳細</a></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><a class="attendance-list__table-detail" href="/admin/attendances/{id}">詳細</a></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><a class="attendance-list__table-detail" href="/admin/attendances/{id}">詳細</a></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><a class="attendance-list__table-detail" href="/admin/attendances/{id}">詳細</a></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><a class="attendance-list__table-detail" href="/admin/attendances/{id}">詳細</a></td>
        </tr>
    </table>
    </div>
</div>
@endsection