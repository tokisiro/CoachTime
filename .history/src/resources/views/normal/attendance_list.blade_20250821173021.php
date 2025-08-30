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
        {{-- 日付選択機能を追加する場合 --}}
        <form action="{{ route('attendances.list') }}" method="GET" id="attendanceMonthForm" class="attendance-list__date">
            <p>
                <button type="submit" name="month" value="{{ \Carbon\Carbon::parse(request('month', now()))->subMonth()->format('Y-m') }}" class= "attendance-list__date-previous">
                    <-前月
                </button>
            </p>
            <div class="attendance-list__date-container">
                <div class="attendance-list__date-container--choice" id="openMonthPicker">
                    <img class="attendance-list__date-container--item" src="/images/calendar.png" alt="カレンダー" >
                    <div id="displayMonth">
                        {{ \Carbon\Carbon::parse(request('month', now()))->format('Y/m') }}
                    </div>
                </div>
                {{-- 実際に値を持つ隠し input type="month" --}}
                <input type="month"
                    id="actualMonthInput"
                    name="month"
                    value="{{ \Carbon\Carbon::parse(request('month', now()))->format('Y-m') }}"
                    style="position: absolute; opacity: 0; pointer-events: none; width: 0; height: 0;">
                </div>
            <p>
                <button type="submit" name="month" value="{{ \Carbon\Carbon::parse(request('month', now()))->addMonth()->format('Y-m') }}" class= "attendance-list__date-next">
                    翌月->
                </button>
            </p>
        </form>
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
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const actualMonthInput = document.getElementById('actualMonthInput');
    const openMonthPicker = document.getElementById('openMonthPicker');
    const displayMonth = document.getElementById('displayMonth');
    const form = document.getElementById('attendanceMonthForm');

    // openMonthPicker (カレンダー画像とテキストのdiv) がクリックされたら
    // 隠してあった actualMonthInput を表示し、フォーカスを当てる
    if (openMonthPicker && actualMonthInput) {
        openMonthPicker.addEventListener('click', function() {
            // actualMonthInput を一時的に visible にして、ブラウザの月選択UIを開かせる
            // ポジションや透明度で隠しつつ、イベントは拾わせる
            actualMonthInput.style.position = 'static'; // 通常の位置に戻すか
            actualMonthInput.style.opacity = '1';      // 透明度を戻す
            actualMonthInput.style.width = 'auto';    // 幅を戻す
            actualMonthInput.style.height = 'auto';   // 高さを戻す
            actualMonthInput.style.pointerEvents = 'auto'; // イベントを受け取るようにする

            actualMonthInput.focus(); // inputにフォーカスを当てる

            // フォーカスが外れたら再び隠す (任意、UXに応じて調整)
            actualMonthInput.addEventListener('blur', function() {
                // actualMonthInput.style.position = 'absolute';
                // actualMonthInput.style.opacity = '0';
                // actualMonthInput.style.width = '0';
                // actualMonthInput.style.height = '0';
                // actualMonthInput.style.pointerEvents = 'none';
                // ★ blurで隠すと選択しにくい場合があるので、基本はchangeでsubmit後に隠れる前提が良い ★
            });
        });
    }

    // input type="month" の値が変更されたら表示を更新し、フォームを送信
    if (actualMonthInput && displayMonth && form) {
        actualMonthInput.addEventListener('change', function() {
            const selectedValue = this.value; // YYYY-MM 形式 (例: 2023-11)
            if (selectedValue) { // 値が選択されていれば
                const [year, month] = selectedValue.split('-');
                displayMonth.textContent = `${year}/${month}`; // 表示を YYYY/MM 形式に更新

                // フォームを送信
                form.submit();
            }
        });
    }

    // 初期表示時に hidden な input の値を表示用の要素に反映
    // この部分は変更不要で、正しく動作するはずです。
    if (actualMonthInput && displayMonth) {
        const initialValue = actualMonthInput.value;
        if (initialValue) {
            const [initialYear, initialMonth] = initialValue.split('-');
            displayMonth.textContent = `${initialYear}/${initialMonth}`;
        }
    }
});
</script>
@endsection