@extends('layouts.list')


@section('css')
<link rel="stylesheet" href="{{ asset('css/normal/attendances_details.css') }}" />
@endsection


@section('content')
<div class="attendance-details">
    <form class="attendance-details__parts" action="{{route('applications', ['id' => $attendance->id]) }}" method="POST" id="attendance-form">
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
                <td>
                    <input class="attendance-details__parts-table--input" type="text" name="check_in_time"
                    value="{{ old('check_in_time',$defaultCheckInTime)}}" {{ $isPendingApproval || $isAttendanceFinalized ? 'disabled' : '' }}>
                </td>
                <td class="attendance-details__parts-table--td">〜</td>
                <td>
                    <input class="attendance-details__parts-table--input" type="text" name="closing_time"
                    value="{{ old('closing_time',$defaultClosingTime )}}" {{ $isPendingApproval || $isAttendanceFinalized ? 'disabled' : '' }}>
                </td>
            </tr>
            @error('check_in_time')
            <tr>
                <th>ERROR</th>
                <td colspan="3">
                    {{$message}}
                </td>
            </tr>
            @enderror
            @foreach($attendance->breaks as $index => $break)
            <tr class="attendance-details__parts-table--existing">
                <th>休憩{{ $index + 1 }}</th>
                <td><input type="hidden" name="breaks[{{ $index }}][id]" value="{{ $break->id }}">
                <input class="attendance-details__parts-table--input break-start-time" type="text" name="breaks[{{ $index }}][start_time]"
                value="{{old('breaks.' . $index . '.start_time', $break->start_time ? \Carbon\Carbon::parse($break->start_time)->format('H:i') : ($break->proposed_start_time ? \Carbon\Carbon::parse($break->proposed_start_time)->format('H:i') : ''))}}" {{ $isPendingApproval || $isAttendanceFinalized ? 'disabled' : '' }}></td>
                <td class="attendance-details__parts-table--td">〜</td>
                <td><input class="attendance-details__parts-table--input break-end-time" type="text" name="breaks[{{ $index }}][end_time]"
                value="{{ old('breaks.' . $index . '.end_time', $break->end_time ? \Carbon\Carbon::parse($break->end_time)->format('H:i') : ($break->proposed_end_time ? \Carbon\Carbon::parse($break->proposed_end_time)->format('H:i') : '')) }} " {{ $isPendingApproval || $isAttendanceFinalized ? 'disabled' : '' }}></td>
            </tr>
            @endforeach
            @if($errors->has('breaks.*.start_time') || $errors->has('breaks.*.end_time'))
            <tr>
                <th>ERROR (休憩)</th>
                <td colspan="3">
                @foreach ($errors->get('breaks.*') as $key => $messages)
                    @foreach($messages as $message)
                        {{ $message }}<br>
                    @endforeach
                @endforeach
                </td>
            </tr>
            @endif
            <tr class="attendance-details__parts-table--new" style="{{ $isPendingApproval ? 'display: none;' : '' }}">
                <th>休憩</th>
                <td><input class="attendance-details__parts-table--input new-break-start-time" type="text" name="new_breaks[0][start_time]"
                value="{{old('new_breaks.0.start_time',$defaultNewBreakStartTime0 )}}" {{ $isPendingApproval || $isAttendanceFinalized ? 'disabled' : '' }}></td>
                <td class="attendance-details__parts-table--td">〜</td>
                <td><input class="attendance-details__parts-table--input new-break-end-time" type="text" name="new_breaks[0][end_time]"
                value="{{old('new_breaks.0.end_time', $defaultNewBreakEndTime0 )}}" {{ $isPendingApproval || $isAttendanceFinalized ? 'disabled' : '' }}></td>
            </tr>
            @if($errors->has('new_breaks.*.start_time') || $errors->has('new_breaks.*.end_time'))
            <tr>
                <th>ERROR (新規休憩)</th>
                <td colspan="3">
                    @foreach ($errors->get('new_breaks.*') as $key => $messages)
                        @foreach($messages as $message)
                            {{ $message }}<br>
                        @endforeach
                    @endforeach
                </td>
            </tr>
            @endif
            <tr>
                <th>備考</th>
                <td colspan="2"><textarea class="attendance-details__parts-table--remarks"  name="remarks" {{ $isPendingApproval || $isAttendanceFinalized ? 'disabled' : '' }}>{{ old('remarks', $defaultRemarks )}}</textarea>
                </td>
            </tr>
            @error('remarks')
            <tr>
                <th>ERROR</th>
                <td>
                    {{$errors->first('remarks')}}
                </td>
            </tr>
            @enderror
        </table>
        <div class="attendance-details__parts-button">
            @if($isPendingApproval)
                <p class="attendance-details__parts-button--pending">* 承認待ちのため修正できません。</p>
            @elseif($isAttendanceFinalized)
            <p class="attendance-details__parts-button--approved">承認済み</p>
            @else
                <button class="attendance-details__parts-button--item" type="submit">
                    修正
                </button>
            @endif
        </div>
    </form>
</div>
@endsection

