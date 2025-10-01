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
    <div>
        {{-- 日付選択機能を追加する場合 --}}
        <form action="{{ route('attendances.list') }}" method="GET" class="attendance-list__date" id="monthSelectionForm">
            <input type="hidden" name="month" id="hiddenMonthInput" value="{{ $selectedMonth->format('Y-m') }}">
            <p>
                <button type="button" class= "attendance-list__date-previous" data-month="{{ \Carbon\Carbon::parse($selectedMonth)->subMonth()->format('Y-m') }}" name="month">
                    <-前月
                </button>
            </p>
            <div class="attendance-list__date-container">
                <input type="month" id="monthSelector" value="{{ $selectedMonth->format('Y-m') }}" class="attendance-list__date-container--calendar">
                <div>
                    {{ \Carbon\Carbon::parse($selectedMonth)->format('Y/m') }}
                </div>
            </div>
            <p>
                <button type="button" data-month="{{ \Carbon\Carbon::parse($selectedMonth)->addMonth()->format('Y-m') }}"
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
                <td>{{$attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') : '-'}}</td>
                <td>{{$attendance->closing_time ? \Carbon\Carbon::parse($attendance->closing_time)->format('H:i') : '-'}}</td>
                <td class="attendance-list__table-">{{$attendance->formatted_total_break_time}}</td>
                <td>{{$attendance->working_minutes}}</td>
                <td><a class="attendance-list__table-detail" href="{{ route('attendance.showDetails', ['id' => $attendance->id]) }}">詳細</a></td>
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

@section('script')
<script>
    document.getElementById('monthSelector').addEventListener('change', function() {
        document.getElementById('monthSelectionForm').submit();
    });

    document.addEventListener('DOMContentLoaded', function() {
        const monthSelectionForm = document.getElementById('monthSelectionForm');
        const monthSelector = document.getElementById('monthSelector');
        const hiddenMonthInput = document.getElementById('hiddenMonthInput');
        const prevMonthButton = document.querySelector('.attendance-list__date-previous');
        const nextMonthButton = document.querySelector('.attendance-list__date-next');

        // type="month" の input が変更されたとき
        monthSelector.addEventListener('change', function() {
            hiddenMonthInput.value = this.value; // hidden input の値を更新
            monthSelectionForm.submit();
        });

        // 前月ボタンがクリックされたとき
        prevMonthButton.addEventListener('click', function() {
            hiddenMonthInput.value = this.dataset.month; // data-month の値を hidden input にセット
            monthSelectionForm.submit();
        });

        // 翌月ボタンがクリックされたとき
        nextMonthButton.addEventListener('click', function() {
            hiddenMonthInput.value = this.dataset.month; // data-month の値を hidden input にセット
            monthSelectionForm.submit();
        });
    });
</script>
@endsection

