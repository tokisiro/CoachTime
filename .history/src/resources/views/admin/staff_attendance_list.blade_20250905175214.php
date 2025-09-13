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
            <th>名前</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><a class="attendance-list__table-detail" href="/admin/attendances/{id}">詳細</a></td>
        </tr>
    </table>
    </div>
</div>
@endsection