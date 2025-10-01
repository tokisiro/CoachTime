@extends('layouts.list')

@section('css')
<link rel="stylesheet" href="{{ asset('css/normal/attendance_register.css') }}" />
@endsection

@section('headerNavigation')
    <div class="header__inner-navigation" id="mainHeaderNavigation">
        {{-- currentStatusが 'finished_work' なら finished バージョン、そうでなければ working バージョン --}}
        @if ($currentStatus === 'finished_work')
            @include('layouts.header_nav')
        @else
            @include('layouts.header_list')
        @endif
    </div>
@endsection

@section('content')
<div class="attendance-register">
    <div class="attendance-register__situation">
        <div class="attendance-register__situation-mark">
            <p class="attendance-register__situation-mark--item">
                @if ($currentStatus === 'before_work')
                    勤務外
                @elseif ($currentStatus === 'working' && !$hasActiveBreak)
                    出勤中
                @elseif ($currentStatus === 'working' && $hasActiveBreak)
                    休憩中
                @elseif ($currentStatus === 'finished_work')
                    退勤済
                @else
                    不明なステータス
                @endif
            </p>
        </div>
        <div class="attendance-register__situation-date">
            {{ $dateStr }}
        </div>
        <div class="attendance-register__situation-time">
            {{ $timeStr }}
        </div>
        <div class="attendance-register__situation-button">
            @if (session('message'))
                <div class="alert alert-success">
                    {{ session('message') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            @if ($currentStatus === 'before_work')
                <form action="{{ route('record.attendance') }}" method="POST" type="submit">
                    @csrf
                    <button class="attendance-register__situation-button--start"  id="startWorkBtn">
                        出勤
                    </button>
                </form>
            @endif
            @if ($currentStatus === 'working' && !$hasActiveBreak)
                <form action="{{ route('record.closing_time') }}" method="POST" type="submit">
                    @csrf
                    <button class="attendance-register__situation-button--end" id="leaveBtn">
                        退勤
                    </button>
                </form>
            @endif
            @if ($currentStatus === 'working' && !$hasActiveBreak)
                <form action="{{ route('record.break_in') }}" method="POST" >
                    @csrf
                    <button class="attendance-register__situation-button--interruption" id="breakInBtn" type="submit">
                        休憩入
                    </button>
                </form>
            @endif
            @if ($hasActiveBreak)
                <form action="{{ route('record.break_back') }}" method="POST">
                    @csrf
                    <button class="attendance-register__situation-button--break" id="breakBackBtn" type="submit">
                        休憩戻
                    </button>
                </form>
            @endif
            @if ($currentStatus === 'finished_work')
            <div class="attendance-register__situation-button--message" id="messageDiv">
                お疲れ様でした
            </div>
            @endif
        </div>
    </div>
</div>
@endsection