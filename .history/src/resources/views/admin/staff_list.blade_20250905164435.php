@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff_list.css') }}" />
@endsection

<!スタッフ一覧(管理者)>
@section('content')
<div class="staff-list">
    <form action="staff-list__parts">
        @csrf
        <h2 class="staff-list__parts-title">
            スタッフ一覧
        </h2>
        <table class="staff-list__parts-table">
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
            @foreach ($staffs as $staff)
            <tr>
                <td>{{$->name}}</td>
                <td>{{$user->email}}</td>
                <td><a class="staff-list__parts-table--detail" href="">詳細</a></td>
            </tr>
            @endforeach
        </table>
</form>
</div>
@endsection