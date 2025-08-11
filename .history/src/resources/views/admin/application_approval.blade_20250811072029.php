@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/admin_attendance_details.css') }}" />
@endsection

<!勤怠詳細画面(管理者)>
@section('content')
<div class="attendance-list">
    <form action="attendance-list__parts">
        <h2 class="attendance-list__parts-title">
            勤怠詳細
        </h2>
        <table class="attendance-list__parts-table">
            <tr>
                <th>名前</th><td></td>
            </tr>
            <tr>
                <th>日付</th><td></td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td><input type="text"></td>
                <td class="attendance-list__parts-table--td">〜</td>
                <td><input type="text"></td>
            </tr>
            <tr>
                <th>休憩</th>
                <td><input type="text"></td>
                <td class="attendance-list__parts-table--td">〜</td>
                <td><input type="text"></td>
            </tr>
            <tr>
                <th>休憩２</th>
                <td><input type="text"></td>
                <td class="attendance-list__parts-table--td">〜</td>
                <td><input type="text"></td>
            </tr>
            <tr>
                <th>備考</th>
                <td><input type="text"></td>
            </tr>
        </table>
        <div class="attendance-list__parts-button">
            <button class="attendance-list__parts-button--item">
                承認
            </button>
        </div>
        </form>
    </div>
</div>
@endsection