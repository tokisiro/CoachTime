@extends('layouts.list')

@section('css')
<link rel="stylesheet" href="{{ asset('css/normal/attendance_register.css') }}" />
@endsection

@section('content')
<div class="attendance-register">
    <div class="attendance-register__situation">
        <div class="attendance-register__situation-mark">
            <p class="attendance-register__situation-mark-item">
                勤務中
            </p>
        </div>
        <div class="attendance-register__situation-date">

        </div>
        <div class="attendance-register__situation-time">

        </div>
        <button class="attendance-register__situation-button">
            出勤
        </button>
    </div>
</div>
@endsection

<script>
    function showDateTime() {
        const now = new Date();
        // 日付と時刻を "YYYY/MM/DD HH:MM:SS" で表示
        const formatted = now.getFullYear() + '/' +
                        String(now.getMonth() + 1).padStart(2, '0') + '/' +
                        String(now.getDate()).padStart(2, '0') + ' ' +
                        String(now.getHours()).padStart(2, '0') + ':' +
                        String(now.getMinutes()).padStart(2, '0') + ':' +
                        String(now.getSeconds()).padStart(2, '0');
        document.getElementById('datetime').textContent = formatted;
    }
    setInterval(showDateTime, 1000); // 毎秒更新
    showDateTime(); // ページ読み込み時にも表示
  </script>