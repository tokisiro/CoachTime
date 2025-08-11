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
        <div class="attendance-register__situation-date" id="datetime">

        </div>
        <div class="attendance-register__situation-time" id="time">

        </div>
        <button class="attendance-register__situation-button">
            出勤
        </button>
    </div>
</div>
@endsection

@section('script')
<script>
    function showDateTime() {
        const now = new Date();
        // 日付部分
      const dateStr = now.getFullYear() + '/' +
                      String(now.getMonth() + 1).padStart(2, '0') + '/' +
                      String(now.getDate()).padStart(2, '0');
      
      // 時間部分
      const timeStr = 
        String(now.getHours()).padStart(2, '0') + ':' +
        String(now.getMinutes()).padStart(2, '0') + ':' +
        String(now.getSeconds()).padStart(2, '0');

      // HTMLに表示
      document.getElementById('date').textContent = dateStr;
      document.getElementById('time').textContent = timeStr;
    }
    setInterval(showDateTime, 1000); // 毎秒更新
    showDateTime(); // ページ読み込み時にも表示
</script>
@endsection