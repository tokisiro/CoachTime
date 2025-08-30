@extends('layouts.list')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/normal/attendance_list.css') }}" />
@endsection

<!勤務一覧(一般)>
@section('content')
<div class="attendance-list">
    <h2 class="attendance-list__title">
        勤怠一覧
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
        @foreach ($attendances as $attendance)
        <tr>
            <td>{{$attendance->user_id}}</td>
            <td>{{$attendance->check_in_time}}</td>
            <td>{{$attendance->closing_time}}</td>
            <td>{{$attendance->}}</td>
            <td>{{$attendance->working_minutes}}</td>
            <td><a class="attendance-list__table-detail" href="/admin/attendances/{id}">詳細</a></td>
        </tr>
        @endforeach
    </table>
    </div>
</div>
@endsection