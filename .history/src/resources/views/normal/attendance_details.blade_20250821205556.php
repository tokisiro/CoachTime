@extends('layouts.list')

@section('css')
<link rel="stylesheet" href="{{ asset('css/normal/attendance_details.css') }}" />
@endsection

<!勤怠詳細画面(一般)(管理)>
@section('content')

<div class="attendance-details">
    <form class="attendance-details__parts" action="" method="POST">
        <h2 class="attendance-details__parts-title">
            勤怠詳細
        </h2>
        <table class="attendance-details__parts-table">
            <tr>
                <th>名前</th>
                <td>{{ $attendance->user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>{{ \Carbon\Carbon::parse($attendance->date)->isoFormat('YYYY年') }}</td>
                
                <td>{{ \Carbon\Carbon::parse($attendance->date)->isoFormat('M月DD日') }}</td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td><input class="attendance-details__parts-table--input" type="text" name="check_in_time" value="{{ $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') : '' }}"></td>
                <td class="attendance-details__parts-table--td">〜</td>
                <td><input class="attendance-details__parts-table--input" type="text" name="closing_time" value="{{ $attendance->closing_time ? \Carbon\Carbon::parse($attendance->closing_time)->format('H:i') : '' }}"></td>
            </tr>
            <tr>
                <th>休憩</th>
                <td><input class="attendance-details__parts-table--input" type="text" name="break_start_time_1" value="{{ $attendance->breaks->get(0) && $attendance->breaks->get(0)->start_time ? \Carbon\Carbon::parse($attendance->breaks->get(0)->start_time)->format('H:i') : '' }}"></td>
                <td class="attendance-details__parts-table--td">〜</td>
                <td><input class="attendance-details__parts-table--input" type="text" name="break_end_time_1" value="{{ $attendance->breaks->get(0) && $attendance->breaks->get(0)->end_time ? \Carbon\Carbon::parse($attendance->breaks->get(0)->end_time)->format('H:i') : '' }}"></td>
            </tr>
            <tr>
                <th>休憩２</th>
                <td><input class="attendance-details__parts-table--input" type="text" name="break_start_time_2" value="{{ $attendance->breaks->get(1) && $attendance->breaks->get(1)->start_time ? \Carbon\Carbon::parse($attendance->breaks->get(1)->start_time)->format('H:i') : '' }}"></td>
                <td class="attendance-details__parts-table--td">〜</td>
                <td><input class="attendance-details__parts-table--input" type="text" name="break_end_time_2" value="{{ $attendance->breaks->get(1) && $attendance->breaks->get(1)->end_time ? \Carbon\Carbon::parse($attendance->breaks->get(1)->end_time)->format('H:i') : '' }}"></td>
            </tr>
            <tr>
                <th>備考</th>
                <td><input class="attendance-details__parts-table--input" type="text" name="remarks" value="{{ $attendance->remarks ?? '' }}"></td>
            </tr>
        </table>
        <div class="attendance-details__parts-button">
            <button class="attendance-details__parts-button--item" type="submit">
                修正
            </button>
        </div>
        </form>
    </div>
</div>

@endsection