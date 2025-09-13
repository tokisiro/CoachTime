@extends('layouts.list')


@section('css')
<link rel="stylesheet" href="{{ asset('css/share/attendance_details.css') }}" />
@endsection


@section('content')
<div class="attendance-details">
    <form class="attendance-details__parts" action="{{ $isAdminLoggedIn ? route('admin.approve', ['id' => $attendance->id]) : route('applications', ['id' => $attendance->id]) }}" method="POST" id="attendance-form">
        @csrf
        <h2 class="attendance-details__parts-title">
            勤怠詳細
        </h2>
        <table class="attendance-details__parts-table">
            <tr>
                <th>名前</th>
                <td class="attendance-details__parts-table--item">{{ $attendance->user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td class="attendance-details__parts-table--item">{{ \Carbon\Carbon::parse($attendance->date)->isoFormat('YYYY年') }}</td>
                <td class="attendance-details__parts-table--td"></td>
                <td class="attendance-details__parts-table--item">{{ \Carbon\Carbon::parse($attendance->date)->isoFormat('M月DD日') }}</td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td><input class="attendance-details__parts-table--input" type="text" name="check_in_time" value="{{ $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i') : '' }}" {{ $isPendingApproval || $isAdminLoggedIn ? 'disabled' : '' }}></td>
                <td class="attendance-details__parts-table--td">〜</td>
                <td><input class="attendance-details__parts-table--input" type="text" name="closing_time" value="{{ $attendance->closing_time ? \Carbon\Carbon::parse($attendance->closing_time)->format('H:i') : '' }}" {{ $isPendingApproval || $isAdminLoggedIn ? 'disabled' : '' }}></td>
            </tr>
            @foreach($attendance->breaks as $index => $break)
            <tr class="attendance-details__parts-table--existing">
                <th>休憩{{ $index + 1 }}</th>
                <td><input type="hidden" name="breaks[{{ $index }}][id]" value="{{ $break->id }}">
                <input class="attendance-details__parts-table--input break-start-time" type="text" name="breaks[{{ $index }}][start_time]" value="{{ $break->start_time ? \Carbon\Carbon::parse($break->start_time)->format('H:i') : '' }}" {{ $isPendingApproval || $isAdminLoggedIn ? 'disabled' : '' }}></td>
                <td class="attendance-details__parts-table--td">〜</td>
                <td><input class="attendance-details__parts-table--input break-end-time" type="text" name="breaks[{{ $index }}][end_time]" value="{{ $break->end_time ? \Carbon\Carbon::parse($break->end_time)->format('H:i') : '' }}" {{ $isPendingApproval || $isAdminLoggedIn ? 'disabled' : '' }}></td>
            </tr>
            @endforeach
            <tr class="attendance-details__parts-table--new" style="{{ $isPendingApproval || $isAdminLoggedIn ? 'display: none;' : '' }}">
                <th>休憩</th>
                <td><input class="attendance-details__parts-table--input new-break-start-time" type="text" name="new_breaks[0][start_time]" value="" {{ $isPendingApproval || $isAdminLoggedIn ? 'disabled' : '' }}></td>
                <td class="attendance-details__parts-table--td">〜</td>
                <td><input class="attendance-details__parts-table--input new-break-end-time" type="text" name="new_breaks[0][end_time]" value="" {{ $isPendingApproval || $isAdminLoggedIn ? 'disabled' : '' }}></td>
            </tr>
            <tr>
                <th>備考</th>
                <td colspan="2"><input class="attendance-details__parts-table--remarks" type="textarea" name="remarks" value="{{ $attendance->remarks ?? '' }}" {{ $isPendingApproval || $isAdminLoggedIn ? 'disabled' : '' }}></td>
            </tr>
        </table>
        <div class="attendance-details__parts-button">
            @if($isPendingApproval)
                
            @elseif($isAdminLoggedIn)
                <button class="attendance-details__parts-button--item" type="submit">
                    承認
                </button>
            @else
                {{-- 承認待ちではない場合、修正ボタンを表示 --}}
                <button class="attendance-details__parts-button--item" type="submit">
                    修正
                </button>
            @endif
        </div>
    </form>
</div>
@endsection

