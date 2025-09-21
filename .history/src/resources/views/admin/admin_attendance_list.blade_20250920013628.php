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
        <form 　action="{{ route('admin.attendances.list') }}" method="GET" class="attendance-list__date">
            <p>
                <button type="submit" name="date" class= "attendance-list__date-previous" value="{{ $previousDay }}">
                    <-前日
                </button>
            </p>
            <div class="attendance-list__date-container">
                <input class="attendance-list__date-container--calendar" type="date" name="selected_date" id="selectedDateInput" value="{{ $displayDate->format('Y-m-d')}}">
                <div>
                    {{ $displayDate->format('Y/m/d') }}
                </div>
            </div>
            <p>
                <button type="submit" name="date" value="{{ $nextDay }}"
                class= "attendance-list__date-next">
                    翌日->
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

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectedDateInput = document.getElementById('selectedDateInput');
    const attendanceDateForm = document.getElementById('attendanceDateForm');

    // 日付入力フィールドの値が変更されたらフォームを送信
    selectedDateInput.addEventListener('change', function () {
        // 'date' パラメータを送信する
        // 'name="selected_date"' を使っているので、ここではその値を新しい 'date' パラメータとして設定する
        // 既存の 'name="date"' ボタンが送信されるのを防ぐため、新しいHiddenフィールドを作成するのが最も安全
        const hiddenDateInput = document.createElement('input');
        hiddenDateInput.type = 'hidden';
        hiddenDateInput.name = 'date'; // コントローラで 'date' パラメータとして受け取るため
        hiddenDateInput.value = this.value; // selectedDateInput の値

        attendanceDateForm.appendChild(hiddenDateInput);
        attendanceDateForm.submit();
    });

    // 前日/翌日ボタンが押されたときも、フォームを送信するが、
    // その際に selected_date の値がメインにならないように注意。
    // 現在のコードではボタンの value が優先されるため、このままでOK。
    // ただし、もし selected_date の値が優先されてしまう場合は、
    // ボタンのクリックイベントで selected_date を無効化するか、valueを空にするなどの処理が必要になる場合があります。
    // 現状のフォーム設計では、name="date"を持つボタンが押された場合、そのボタンのvalueが優先されるため問題ありません。
});
</script>
@endsection