@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff_application_list.css') }}" />
@endsection

<!申請一覧(一般)(管理者)>
@section('content')
<div class="application-list">
    <div class="application-list__parts">
        <h2 class="application-list__parts-title">
            申請一覧
        </h2>
        <div class="application-list__parts-switching">
            <a class="application-list__parts-switching--link" href="">承認待ち</a>
            <a class="application-list__parts-switching--link" href="">承認済み</a>
        </div>
        <table class="application-list__parts-table">
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td><a class="application-list__parts-table--detail" href="">詳細</a></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td><a class="application-list__parts-table--detail" href="">詳細</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td><a class="application-list__parts-table--detail" href="">詳細</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td><a class="application-list__parts-table--detail" href="">詳細</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td><a class="application-list__parts-table--detail" href="">詳細</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td><a class="application-list__parts-table--detail" href="">詳細</td>
            </tr>
        </table>
    </div>
</div>
@endsection