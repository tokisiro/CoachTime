@extends('layouts.admin')


@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/admin_attendance_details.css') }}" />
@endsection


@section('content')
<div class="attendance-details">
    <form class="attendance-details__parts" action="{{ route('admin.approve', ['id' => $attendance->id]) }}" method="POST" id="attendance-form">
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
                <td><input class="attendance-details__parts-table--input" type="text" name="check_in_time"
                value="{{ old('check_in_time',
                    $isPendingApproval
                        ? (\Carbon\Carbon::parse($pendingApplication->proposed_check_in_time)?->format('H:i') ?? '')
                        : (
                            (\Carbon\Carbon::parse($latestApprovedApplication?->proposed_check_in_time)?->format('H:i') ?? '') ?:
                            (\Carbon\Carbon::parse($attendance->check_in_time)?->format('H:i') ?? '')
                        )
                ) }}" {{ $isPendingApproval || $isAttendanceFinalized ? 'readonly' : '' }}></td>
                <td class="attendance-details__parts-table--td">〜</td>
                <td><input class="attendance-details__parts-table--input" type="text" name="closing_time"
                value="{{ old('closing_time',
                    $isPendingApproval
                        ? (\Carbon\Carbon::parse($pendingApplication->proposed_closing_time)?->format('H:i') ?? '')
                        : (
                            (\Carbon\Carbon::parse($latestApprovedApplication?->proposed_closing_time)?->format('H:i') ?? '') ?:
                            (\Carbon\Carbon::parse($attendance->closing_time)?->format('H:i') ?? '')
                        )
                ) }}" {{ $isPendingApproval || $isAttendanceFinalized ? 'readonly' : '' }}></td>
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
                <td>
                    <input type="hidden" name="breaks[{{ $index }}][id]" value="{{ $break->id }}">
                    <input class="attendance-details__parts-table--input break-start-time" type="text" name="breaks[{{ $index }}][start_time]"
                    value="{{ old('breaks.' . $index . '.start_time',
                        (\Carbon\Carbon::parse($break->start_time)?->format('H:i') ?? '') ?:
                        (\Carbon\Carbon::parse($break->proposed_start_time)?->format('H:i') ?? '')
                    ) }}" {{ $isPendingApproval || $isAttendanceFinalized ? 'readonly' : '' }}>
                </td>
                    <td class="attendance-details__parts-table--td">〜</td>
                <td>
                    <input class="attendance-details__parts-table--input break-end-time" type="text" name="breaks[{{ $index }}][end_time]"
                    value="{{ old('breaks.' . $index . '.end_time',
                        (\Carbon\Carbon::parse($break->end_time)?->format('H:i') ?? '') ?:
                        (\Carbon\Carbon::parse($break->proposed_end_time)?->format('H:i') ?? '')
                    ) }}" {{ $isPendingApproval || $isAttendanceFinalized ? 'readonly' : '' }}>
                </td>
            </tr>
            @php
                // この休憩のstart_timeとend_timeに関するエラーをまとめて取得
                $breakErrors = collect([]);
                if ($errors->has('breaks.' . $index . '.start_time')) {
                $breakErrors = $breakErrors->merge($errors->get('breaks.' . $index . '.start_time'));
                }
                if ($errors->has('breaks.' . $index . '.end_time')) {
                $breakErrors = $breakErrors->merge($errors->get('breaks.' . $index . '.end_time'));
                }
            @endphp

            @if($breakErrors->isNotEmpty())
            <tr class="attendance-details__error-row">
                <th>ERROR (休憩{{ $index + 1 }})</th>
                <td colspan="3">
                @foreach ($breakErrors as $message)
                    <p>{{ $message }}</p>
                @endforeach
                </td>
            </tr>
            @endif
            @endforeach
            <tr class="attendance-details__parts-table--new" style="{{ ($isPendingApproval || $isAttendanceFinalized) ? 'display: none;' : '' }}">
                <th>休憩</th>
                <td><input class="attendance-details__parts-table--input new-break-start-time" type="text" name="new_breaks[0][start_time]"
                value="{{ old('new_breaks.0.start_time',
                    $isPendingApproval && !empty($pendingApplication->new_proposed_breaks[0])
                        ? (\Carbon\Carbon::parse($pendingApplication->new_proposed_breaks[0]['start_time'])?->format('H:i') ?? '')
                        : (
                            (!empty($latestApprovedApplication->new_proposed_breaks[0])
                                ? (\Carbon\Carbon::parse($latestApprovedApplication->new_proposed_breaks[0]['start_time'])?->format('H:i') ?? '')
                                : ''
                            )
                        )
                ) }}" {{ $isPendingApproval || $isAttendanceFinalized ? 'readonly' : '' }}></td>
                <td class="attendance-details__parts-table--td">〜</td>
                <td><input class="attendance-details__parts-table--input new-break-end-time" type="text" name="new_breaks[0][end_time]"
                value="{{ old('new_breaks.0.end_time',
                    $isPendingApproval && !empty($pendingApplication->new_proposed_breaks[0])
                        ? (\Carbon\Carbon::parse($pendingApplication->new_proposed_breaks[0]['end_time'])?->format('H:i') ?? '')
                        : (
                            (!empty($latestApprovedApplication->new_proposed_breaks[0])
                                ? (\Carbon\Carbon::parse($latestApprovedApplication->new_proposed_breaks[0]['end_time'])?->format('H:i') ?? '')
                                : ''
                            )
                        )
                ) }}" {{ $isPendingApproval || $isAttendanceFinalized ? 'readonly' : '' }}></td>
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
                <td colspan="2"><textarea class="attendance-details__parts-table--remarks" name="remarks"
                {{ old('remarks',
                    $isPendingApproval
                        ? ($pendingApplication->proposed_remarks ?? '')
                        : ($latestApprovedApplication?->proposed_remarks ?? $attendance->remarks ?? '')
                ) }}" {{ $isPendingApproval || $isAttendanceFinalized ? 'readonly' : '' }}></textarea>
                </td>
            </tr>
            @error('remarks')
            <tr>
                <th>ERROR</th>
                <td colspan="3">
                    {{$errors->first('remarks')}}
                </td>
            </tr>
            @enderror
        </table>
        <div class="attendance-details__parts-button">
            @if($isPendingApproval)
                <button class="attendance-details__parts-button--item" type="submit">
                    承認
                </button>
            @elseif($isAttendanceFinalized)
            <p class="attendance-details__parts-button--approved-message">承認済み</p>
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

