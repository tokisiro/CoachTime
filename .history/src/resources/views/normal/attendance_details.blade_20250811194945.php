@extends('layouts.list')

@section('css')
<link rel="stylesheet" href="{{ asset('css/normal/attendance_details.css') }}" />
@endsection

<!勤怠詳細画面(一般)>
@section('content')
<div class="attendance-details">
    <form action="attendance-details__parts">
        <h2 class="attendance-details__parts-title">
            勤怠詳細
        </h2>
        <table class="attendance-details__parts-table">
            <tr>
                <th>名前</th><td></td>
            </tr>
            <tr>
                <th>日付</th><td></td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td><input type="text"></td>
                <td class="attendance-details__parts-table--td">〜</td>
                <td><input type="text"></td>
            </tr>
            <tr>
                <th>休憩</th>
                <td><input type="text"></td>
                <td class="attendance-details__parts-table--td">〜</td>
                <td><input type="text"></td>
            </tr>
            <tr>
                <th>休憩２</th>
                <td><input type="text"></td>
                <td class="attendance-details__parts-table--td">〜</td>
                <td><input type="text"></td>
            </tr>
            <tr>
                <th>備考</th>
                <td><input type="text"></td>
            </tr>
        </table>
        <div class="attendance-details__parts-button">
            <button class="attendance-details__parts-button--item">
                修正
            </button>
        </div>
        </form>
    </div>
</div>
@endsection