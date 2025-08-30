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
            <a class="application-list__parts-switching--link {{ $status === 'pending' ? 'is-active' : '' }}" href="{{ route('showApplicationsList', ['status' => 'pending']) }}">承認待ち</a>
            <a class="application-list__parts-switching--link {{ $status === 'approved' ? 'is-active' : '' }}" href="{{ route('showApplicationsList', ['status' => 'approved']) }}">承認済み</a>
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
            <tbody>
                @forelse($applications as $application)
                <tr>
                    <td>
                        @if($application->status === 'pending')
                            承認待ち
                        @elseif($application->status === 'approved')
                            承認済み
                        @else
                            {{ $application->status }}
                        @endif
                    </td>
                    <td>{{ $application->user->name }}</td>
                    <td>
                        @if($application->attendance && $application->attendance->date)
                            {{ \Carbon\Carbon::parse($application->attendance->date)->format('Y/m/d') }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td>{{ $application->reason }}</td>
                    <td>{{ $application->created_at->format('Y-m-d') }}</td>
                    <td>
                        {{-- attendance が null でないことを確認する --}}
                        @if($application->attendance)
                            <a class="application-list__parts-table--detail"
                                href="{{ route('attendance.showDetails', ['id' => $application->attendance->id]) }}">詳細</a>
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">承認前の申請はありません。</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        {{-- ページネーションリンク --}}
        <div class="pagination">
            {{ $applications->appends(['status' => $status])->links() }}
        </div>
    </div>
</div>
@endsection