@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/.attendance-list {
    background-color: rgb(236, 235, 235);
    max-width: 100%;
}

.attendance-list__parts-title {
    padding: 20px 40px;
    margin: 3px auto;
    width: 70%;
}

.attendance-list__parts-table {
    margin: 3px auto;
    background-color: white;
    width: 70%;
    border-collapse: collapse;
    color: gray;
    border-radius: 10px;
}

.attendance-list__parts-table th,
.attendance-list__parts-table td {
    padding: 8px 30px;
    text-align: center;
}

.attendance-list__parts-table tr {
    border-bottom: 1px solid #ccc;
}

.attendance-list__parts-table--detail {
    text-decoration: none;
    color: black;
    font-weight: bold;
}_list.css') }}" />
@endsection

<!スタッフ一覧(管理者)>
@section('content')
<div class="attendance-list">
    <div action="attendance-list__parts">
        <h2 class="attendance-list__parts-title">
            申請一覧
        </h2>
        <table class="attendance-list__parts-table">
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td><a class="attendance-list__parts-table--detail" href="">詳細</a></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td><a class="attendance-list__parts-table--detail" href="">詳細</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td><a class="attendance-list__parts-table--detail" href="">詳細</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td><a class="attendance-list__parts-table--detail" href="">詳細</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td><a class="attendance-list__parts-table--detail" href="">詳細</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td><a class="attendance-list__parts-table--detail" href="">詳細</td>
            </tr>
        </table>
    </div>
</div>
@endsection