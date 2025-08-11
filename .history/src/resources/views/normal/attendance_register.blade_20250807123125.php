@extends('layouts.list')

@section('css')
<link rel="stylesheet" href="{{ asset('css/normal/attendance_register.css') }}" />
@endsection

@section('content')
<div class="attendance-register">
    <div class="attendance-register__situation">
        <div class="attendance-register__situation-mark">
            <p class="attendance-register__situation-mark-item">
                勤務中
            </p>
        </div>
        <div class="attendance-register__situation-date">

        </div>
        <div class="attendance-register__situation-time">

        </div>
        <button class="attendance-register__situation">

        </button>
    </div>
</div>
@endsection