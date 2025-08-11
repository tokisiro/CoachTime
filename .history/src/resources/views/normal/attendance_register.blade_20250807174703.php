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
        <div class="attendance-register__situation-button">
            <button class="attendance-register__situation-button" id="statusBtn" id="startWorkBtn">出勤</button>
            <button id="leaveBtn" style="display:none;">退勤</button>
            <button id="breakInBtn" style="display:none;">休憩入</button>
            <button id="breakBackBtn" style="display:none;">休憩戻</button>
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

    //ボタンの表示切り替え設定
    const startWorkBtn = document.getElementById('startWorkBtn');
    const leaveBtn = document.getElementById('leaveBtn');
    const breakInBtn = document.getElementById('breakInBtn');
    const breakBackBtn = document.getElementById('breakBackBtn');
    const messageDiv = document.getElementById('message');

startWorkBtn.addEventListener('click', () => {
  // 出勤ボタンを隠す
    startWorkBtn.style.display = 'none';
  // 退勤と休憩入ボタンを表示
    leaveBtn.style.display = 'inline-block';
    breakInBtn.style.display = 'inline-block';
});

// 退勤ボタン
leaveBtn.addEventListener('click', () => {
    messageDiv.textContent = 'お疲れ様でした';
  // ボタンを非表示にするか、またはリセット
    leaveBtn.style.display = 'none';
    breakInBtn.style.display = 'none';
    breakBackBtn.style.display = 'none';
});

// 休憩入
breakInBtn.addEventListener('click', () => {
  breakInBtn.style.display = 'none'; // 休憩入を隠す
  breakBackBtn.style.display = 'inline-block'; // 休憩戻りを表示
});

// 休憩戻り
breakBackBtn.addEventListener('click', () => {
  breakBackBtn.style.display = 'none'; // 休憩戻りを隠す
  breakInBtn.style.display = 'inline-block'; // 休憩入を表示
  // ちなみに退勤ボタンは引き続き表示されている状態（必要なら調整）
});
</script>
@endsection