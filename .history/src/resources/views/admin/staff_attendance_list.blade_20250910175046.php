@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff_attendance_list.css') }}" />
@endsection

<!スタッフ別勤怠一覧(管理者)>
@section('content')
<div class="attendance-list">
    <h2 class="attendance-list__title">
        {{ $staff->name }}さんの勤怠
    </h2>
    <div class="attendance-list__date">
        <a href="{{ $prevMonthUrl }}" class="attendance-list__date-nav">
            <-前月
        </a>
        <p class="attendance-list__date-current">
            {{ $year }}年{{ $month }}月
        </p>
        <a href="{{ $nextMonthUrl }}" class="attendance-list__date-nav">
            翌月->
        </a>
    </div>
    <table class="attendance-list__table">
        <thead>
            <tr>
                <th>日付</th>
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
                <td>{{ $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') : '' }}</td>
                <td>{{ $attendance->closing_time ? \Carbon\Carbon::parse($attendance->closing_time)->format('H:i') : '' }}</td>
                <td>
                    @php
                        $totalBreakMinutes = 0;
                        if ($attendance->check_in_time && $attendance->closing_time) {
                        foreach ($attendance->breaks as $break) {
                            if ($break->start_time && $break->end_time) {
                                $breakStart = \Carbon\Carbon::parse($break->start_time);
                                $breakEnd = \Carbon\Carbon::parse($break->end_time);
                                $totalBreakMinutes += $breakEnd->diffInMinutes($breakStart);
                            }
                        }
                        echo sprintf('%02d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60);
                    } else {
                        echo ''; // 出勤・退勤がない場合は空欄
                        }
                    @endphp
                </td>
                <td>
                    @php
                        $workingMinutes = 0;
                        if ($attendance->check_in_time && $attendance->closing_time) {
                            $checkIn = \Carbon\Carbon::parse($attendance->check_in_time);
                            $closing = \Carbon\Carbon::parse($attendance->closing_time);
                            $workingMinutes = $closing->diffInMinutes($checkIn) - $totalBreakMinutes;
                        }
                        echo sprintf('%02d:%02d', floor($workingMinutes / 60), $workingMinutes % 60);
                    @endphp
                </td>
                <td>
                    <a class="attendance-list__table-detail"
                    href="{{ route('attendance.showDetails', ['id' => $attendance->id]) }}">詳細</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6">この月の勤怠データはありません。</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection