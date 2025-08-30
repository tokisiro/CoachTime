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
                <button type="submit" name="month" value="{{ \Carbon\Carbon::parse(request('month', now()))->subMonth()->format('Y-m') }}"
                class= "attendance-list__date-previous">
                    <-前月
                </button>
            </p>
        </form>
        <div class="month-display-container">
            <img src="/images/calendar.png" alt="カレンダー">
            <div id="displayMonth">
                {{ \Carbon\Carbon::parse(request('month', now()))->format('Y/m') }}
            </div>
        </div>
        {{-- 実際に値を持つ隠し input type="month" --}}
        <input
            type="month"
            id="actualMonthInput"
            name="month"
            value="{{ \Carbon\Carbon::parse(request('month', now()))->format('Y-m') }}"
            style="display: none;" {{-- 通常は非表示 --}}>
        <form action="{{ route('attendances.list') }}" method="GET">
            <p>
                <button type="submit" name="month" value="{{ \Carbon\Carbon::parse(request('month', now()))->addMonth()->format('Y-m') }}"
                class= "attendance-list__date-next">
                    翌月->
                </button>
            </p>
        </form>
    </div>
    <table class="attendance-list__table">
        <tr>
            <th>日付</th>
            <th>名前</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
        </tr>
        @php
            $currentDate = null;
        @endphp
        @foreach ($attendances as $attendance)

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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const actualMonthInput = document.getElementById('actualMonthInput');
    const openMonthPicker = document.getElementById('openMonthPicker');
    const displayMonth = document.getElementById('displayMonth');
    const form = document.getElementById('attendanceMonthForm');

    // アイコンがクリックされたら非表示のinput type="month" を開く
    openMonthPicker.addEventListener('click', function() {
        actualMonthInput.click(); // input type="month" をクリックしてUIを開く
    });

    // input type="month" の値が変更されたら表示を更新し、フォームを送信
    actualMonthInput.addEventListener('change', function() {
        const selectedValue = this.value; // YYYY-MM 形式
        const [year, month] = selectedValue.split('-');
        displayMonth.textContent = `${year}/${month}`; // 表示を YYYY/MM 形式に更新

        form.submit(); // フォームを送信
    });

    // 初期表示時に hidden な input の値を表示用の要素に反映
    const initialValue = actualMonthInput.value;
    const [initialYear, initialMonth] = initialValue.split('-');
    displayMonth.textContent = `${initialYear}/${initialMonth}`;
});
</script>
@endpush