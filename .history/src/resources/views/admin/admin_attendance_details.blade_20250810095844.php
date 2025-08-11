@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/admin_attendance_list.css') }}" />
@endsection

<!勤怠詳細画面(管理者)>
@section('content')
<div class="attendance-list">
    <h2 class="attendance-list__title">
        勤怠詳細
    </h2>
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
            <td><a class="attendance-list__table-detail" href="">詳細</a></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><a class="attendance-list__table-detail" href="">詳細</a></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><a class="attendance-list__table-detail" href="">詳細</a></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><a class="attendance-list__table-detail" href="">詳細</a></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><a class="attendance-list__table-detail" href="">詳細</a></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><a class="attendance-list__table-detail" href="">詳細</a></td>
        </tr>
    </table>
    </div>
</div>
@endsection