@extends('layouts.list')

@section('css')
<link rel="stylesheet" href="{{ asset('css/normal/attendance_register.css') }}" />
@endsection

@section('headerNavigation')
    <div class="header__inner-navigation" id="mainHeaderNavigation">
        {{-- currentStatusが 'finished_work' なら finished バージョン、そうでなければ working バージョン --}}
        @if ($currentStatus === 'finished_work')
            @include('layouts.header_list')
        @else
            @include('partials.header_nav')
        @endif
    </div>
@endsection

@section('content')
<div class="attendance-register">
    <div class="attendance-register__situation">
        <div class="attendance-register__situation-mark">
            <p class="attendance-register__situation-mark--item">
                勤務外
            </p>
        </div>
        <div class="attendance-register__situation-date">
            //リアル日時
        </div>
        <div class="attendance-register__situation-time">
            //リアルタイム
        </div>
        <div class="attendance-register__situation-button">
            <button class="attendance-register__situation-button--start"  id="startWorkBtn" >出勤</button>
            <button class="attendance-register__situation-button--end" id="leaveBtn">退勤</button>
            <button class="attendance-register__situation-button--interruption" id="breakInBtn" >休憩入</button>
            <button class="attendance-register__situation-button--break" id="breakBackBtn">休憩戻</button>
            <div class="attendance-register__situation-button--message" id="messageDiv">お疲れ様でした</div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    //リアルタイム日時の表示設定
    function showDateTime() {
        const now = new Date();
        // 日付部分
        const daysOfWeek = ['日', '月', '火', '水', '木', '金', '土'];
        const day = daysOfWeek[now.getDay()];
        const dateStr = now.getFullYear() + '年' +
                        String(now.getMonth() + 1). padStart(1,) + '月' +
                        String(now.getDate()).padStart(1,)+'日(' + day + ')';

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



    //ボタンの表示切り替え設定
    const startWorkBtn = document.getElementById('startWorkBtn');
    const leaveBtn = document.getElementById('leaveBtn');
    const breakInBtn = document.getElementById('breakInBtn');
    const breakBackBtn = document.getElementById('breakBackBtn');
    const statusText = document.querySelector('.attendance-register__situation-mark--item');
    const messageDiv = document.getElementById('messageDiv');

    //ボタンと状況を更新する
    function updateButtonStates(currentStatus,  hasActiveBreak = false) {
        //初期表示
        startWorkBtn.style.display = 'none';
        leaveBtn.style.display = 'none';
        breakInBtn.style.display = 'none';
        breakBackBtn.style.display = 'none';
        messageDiv.style.display = 'none';

    //出勤前
    if (currentStatus === 'before_work') {
            startWorkBtn.style.display = 'inline-block';
            statusText.textContent = '勤務外';
        //勤務中
        } else if (currentStatus === 'working') {
            leaveBtn.style.display = 'inline-block';
            statusText.textContent = '出勤中';
            //休憩中
            if (hasActiveBreak) {
                leaveBtn.style.display = 'none';
                breakBackBtn.style.display = 'inline-block';
                statusText.textContent = '休憩中';
            } else {
                breakInBtn.style.display = 'inline-block';
            }
            //退勤済み
        } else if (currentStatus === 'finished_work') {
            messageDiv.style.display = 'inline-block';
            statusText.textContent = '退勤済';
        }
    }

    // サーバーから渡された今日の出勤状況
    const initialStatus = "{{ $currentStatus }}";
    const initialHasActiveBreak = {{ $hasActiveBreak ? 'true' : 'false' }};
    updateButtonStates(initialStatus, initialHasActiveBreak);

    //出勤時間登録機能
    startWorkBtn.addEventListener('click', () => {
    // 非同期で出勤時間をデータベースに保存
    fetch('/record-attendance', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}' // LaravelのCSRFトークン
        },
        body: JSON.stringify({}) // 送信するデータがない場合は空のオブジェクト
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            updateButtonStates('working', false);
        } else {
            console.error('出勤記録に失敗しました:', data.message);
            alert('出勤記録に失敗しました。');
        }
    })
    .catch(error => {
        console.error('エラー:', error);
        alert('エラーが発生しました。');
    });
});


// 退勤時間登録機能
leaveBtn.addEventListener('click', () => {
    // 非同期で退勤時間をデータベースに保存
    fetch('/record-closing-time', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}' // LaravelのCSRFトークン
        },
        body: JSON.stringify({}) // 送信するデータがない場合は空のオブジェクト
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
                updateButtonStates('finished_work');

            const mainHeaderNavigation = document.getElementById('mainHeaderNavigation');
            if (mainHeaderNavigation && data.finished_nav_html) {
                mainHeaderNavigation.innerHTML = data.finished_nav_html;
            }

        } else {
            console.error('退勤記録に失敗しました:', data.message);
            alert('退勤記録に失敗しました。');
        }
    })
    .catch(error => {
        console.error('エラー:', error);
        alert('エラーが発生しました。');
    });
});


// 休憩入
breakInBtn.addEventListener('click', () => {
    fetch('/record-break-in', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            updateButtonStates('working', true);
        } else {
            console.error('休憩開始の記録に失敗しました:', data.message);
            alert('休憩開始の記録に失敗しました。');
        }
    })
    .catch(error => {
        console.error('エラー:', error);
        alert('エラーが発生しました。');
    });
});

// 休憩戻り
breakBackBtn.addEventListener('click', () => {
    fetch('/record-break-back', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            updateButtonStates('working', false); // 出勤中（休憩終了）
        } else {
            console.error('休憩終了の記録に失敗しました:', data.message);
            alert('休憩終了の記録に失敗しました。');
        }
    })
    .catch(error => {
        console.error('エラー:', error);
        alert('エラーが発生しました。');
    });
});
</script>
@endsection