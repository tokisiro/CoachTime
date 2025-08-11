@extends('layouts.admin')

//管理者勤怠一覧画面

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/admin_attendance_list.css') }}" />
@endsection

@section('content')
    <table>
        <tr>
            <th>名前</th>
            <th>出勤</th>
            <th>退勤</th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
    </table>
@endsection