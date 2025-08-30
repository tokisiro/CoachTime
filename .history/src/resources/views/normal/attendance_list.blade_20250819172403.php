@extends('layouts.list')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/normal/attendance_list.css') }}" />
@endsection

<!勤務一覧(一般)>
@section('content')
<div class="attendance">
<div class="attendance-list">
    <h2 class="attendance-list__title">
        勤怠一覧
    </h2>
    <div class="attendance-list__date">
        {{-- 日付選択機能を追加する場合 --}}
        <form action="{{ route('attendances.list') }}" method="GET">
            <p>
                <button type="submit" name="date" value="{{ \Carbon\Carbon::parse(request('date', now()))->subDay()->toDateString() }}">
                    <-前月
                </button>
            </p>
        </form>
        <p>
            {{ \Carbon\Carbon::parse(request('date', now()))->format('Y年m月d日') }}
        </p>
        <form action="{{ route('attendances.list') }}" method="GET">
            <p>
                <button type="submit" name="date" value="{{ \Carbon\Carbon::parse(request('date', now()))->addDay()->toDateString() }}">
                    翌月->
                </button>
            </p>
        </form>
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
        @foreach ($attendances as $attendance)
        <tr>
            <td>{{$attendance->user->name}}</td>
            <td>{{$attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') : '-'}}</td>
            <td>{{$attendance->closing_time ? \Carbon\Carbon::parse($attendance->closing_time)->format('H:i') : '-'}}</td>
            <td>{{$attendance->total_break_minutes}}分</td>
            <td>{{$attendance->working_minutes}}分</td>
            <td><a class="attendance-list__table-detail" href="{{ route('attendances.show', ['id' => $attendance->id]) }}">詳細</a></td>
        </tr>
        @endforeach
    </table>
    </div>
</div>
</div>
@endsection