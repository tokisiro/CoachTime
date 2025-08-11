@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff_attendance_list.css') }}" />
@endsection

<!スタッフ勤怠(管理者)>
@section('content')
<div class="attendance-list">
    <div action="attendance-list__parts">
        <h2 class="attendance-list__parts-title">
            スタッフ一覧
        </h2>
        <table class="attendance-list__parts-table">
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td><a class="attendance-list__parts-table--detail" href="">詳細</a></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td><a class="attendance-list__parts-table--detail" href="">詳細</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td><a class="attendance-list__parts-table--detail" href="">詳細</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td><a class="attendance-list__parts-table--detail" href="">詳細</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td><a class="attendance-list__parts-table--detail" href="">詳細</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td><a class="attendance-list__parts-table--detail" href="">詳細</td>
            </tr>
        </table>
    </div>
</div>
@endsection