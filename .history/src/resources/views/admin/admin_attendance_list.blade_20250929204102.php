@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/admin_attendance_list.css') }}" />
@endsection

<!勤怠一覧(管理者)>
@section('content')
<div class="attendance">
<div class="attendance-list">
    <h2 class="attendance-list__date-heading">
        {{ $displayDate->format('Y年m月d日') }}の勤怠
    </h2>
    <div>
        {{-- 日付選択機能を追加する場合 --}}
        <form id="attendanceDateForm" action="{{ route('admin.attendances.list') }}" method="GET" class="attendance-list__date">
            <p>
                <button type="submit" name="date" class= "attendance-list__date-previous" value="{{ $previousDay }}">
                    &lt;-前日
                </button>
            </p>
            <div class="attendance-list__date-container">
                <input class="attendance-list__date-container--calendar" type="date" name="date" id="selectedDateInput" value="{{ $displayDate->format('Y-m-d')}}">
                <div>
                    {{ $displayDate->format('Y/m/d') }}
                </div>
            </div>
            <p>
                <button type="submit" name="date" value="{{ $nextDay }}"
                class= "attendance-list__date-next">
                    翌日->-&gt;
                </button>
            </p>
        </form>
    </div>
    <table class="attendance-list__table">
        <thead>
            <tr>
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
                <td>{{$attendance->user->name}}</td>
                <td>{{$attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') : '-'}}</td>
                <td>{{$attendance->closing_time ? \Carbon\Carbon::parse($attendance->closing_time)->format('H:i') : '-'}}</td>
                <td>{{$attendance->formatted_total_break_time}}</td>
                <td>{{$attendance->working_minutes}}</td>
                <td><a class="attendance-list__table-detail" href="{{ route('admin.showDetails', ['id' => $attendance->id]) }}">詳細</a></td>
            </tr>
            @empty
            <tr>
                <td colspan="7">選択された月には勤怠記録がありません。</td>
            </tr>
                @endforelse
        </tbody>
    </table>
</div>
</div>
@endsection

