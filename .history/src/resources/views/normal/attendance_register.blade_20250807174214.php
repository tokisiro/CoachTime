@extends('layouts.list')

@section('css')
<link rel="stylesheet" href="{{ asset('css/normal/attendance_register.css') }}" />
@endsection

@section('content')
<div class="attendance-register">
    <div class="attendance-register__situation">
        <div class="attendance-register__situation-mark">
            <p class="attendance-register__situation-mark-item">
                勤務外
            </p>
        </div>
        <div class="attendance-register__situation-date">
        </div>
        <div class="attendance-register__situation-time">
        </div>
        <div>
            <button class="attendance-register__situation-button" id="statusBtn" id="startWorkBtn">
            <button id="leaveBtn" style="display:none;">退勤</button>
            <button id="breakInBtn" style="display:none;">休憩入</button>
            <button id="breakBackBtn" style="display:none;">休憩戻り</button>
            出勤
            </button>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    function showDateTime() {
        const now = new Date();
        // 日付部分
        const dateStr = now.getFullYear() + '年' +
                        String(now.getMonth() + 1). padStart(1,) + '月' +
                        String(now.getDate()).padStart(1,)+'日';

      // 時間部分
        const timeStr =
        String(now.getHours()).padStart(2, '0') + ':' +
        String(now.getMinutes()).padStart(2, '0');

      // HTMLに表示
        document.querySelector('.attendance-register__situation-date').textContent = dateStr;
        document.querySelector('.attendance-register__situation-time').textContent = timeStr;
    }
    setInterval(showDateTime, 1000); // 毎秒更新
    showDateTime(); // ページ読み込み時にも表示

    // 出勤ボタンを押したら状況を変更
    document.querySelector('.attendance-register__situation-button').addEventListener('click', () => {
        const statusItem = document.querySelector('.attendance-register__situation-mark-item');
        statusItem.textContent = '勤務中';
    });
</script>
@endsection