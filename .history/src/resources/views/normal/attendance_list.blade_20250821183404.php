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
        <form action="{{ route('attendances.list') }}" method="GET" id="monthNavigationForm">
            <p>
                <button type="submit" name="date" class= "attendance-list__date-previous" value="{{ \Carbon\Carbon::parse(request($selectedMonth)->subMonth()->format('Y-m') }}">
                    <-前月
                </button>
            </p>
            <div class="attendance-list__date-container">
                <img class="attendance-list__date-container--item" src="/images/calendar.png" alt="カレンダー">
                <div id="displayMonth">
                    {{ \Carbon\Carbon::parse(request($selectedMonth)->format('Y/m') }}
                </div>
            </div>
            <p>
                <button type="submit" name="month" value="{{ \Carbon\Carbon::parse(request($selectedMonth)->addMonth()->format('Y-m') }}"
                class= "attendance-list__date-next">
                    翌月->
                </button>
            </p>
        </form>
    </div>
    <table class="attendance-list__table">
        <thead>
            <tr>
                <th>日付</th>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($attendances as $attendance)
        <tr>
            <td>{{ \Carbon\Carbon::parse($attendance->date)->isoFormat('MM/DD(ddd)') }}</td>
            <td>{{$attendance->user->name}}</td>
            <td>{{$attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') : '-'}}</td>
            <td>{{$attendance->closing_time ? \Carbon\Carbon::parse($attendance->closing_time)->format('H:i') : '-'}}</td>
            <td>{{$attendance->formatted_total_break}}</td>
            <td>{{$attendance->formatted_working_time}}</td>
            <td><a class="attendance-list__table-detail" href="{{ route('attendance.show', ['id' => $attendance->id]) }}">詳細</a></td>
        </tr>
        @endforeach
    </table>
    </div>
</div>
</div>
@endsection

@section('scripts')
<script>

</script>
@endsection