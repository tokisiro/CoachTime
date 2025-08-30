@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/share/application_list.css') }}" />
@endsection

<!申請一覧(一般)(管理者)>
@section('content')
<div class="application-list">
    <div class="application-list__parts">
        <h2 class="application-list__parts-title">
            申請一覧
        </h2>
        <div class="application-list__parts-switching">
            <a class="application-list__parts-switching--link {{ $status === 'pending' ? 'is-active' : '' }}" href="{{ route(Route::showApplicationsList(), ['status' => 'pending']) }}">承認待ち</a>
            <a class="application-list__parts-switching--link {{ $status === 'approved' ? 'is-active' : '' }}" href="{{ route(Route::showApplicationsList(), ['status' => 'approved']) }}">承認済み</a>
        </div>
        <table class="application-list__parts-table">
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                <th>詳細</th>
            </tr>
            </thead>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td><a class="application-list__parts-table--detail" href="{{ route('attendance.show', ['id' => $applications->attendance_id]) }}">詳細</a></td>
            </tr>
        </table>
    </div>
</div>
@endsection