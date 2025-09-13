<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
    // 出勤時間と退勤時間の関係をチェックする
    Validator::extend('check_in_and_closing_times_valid', function ($attribute, $value, $parameters, $validator) {
        $data = $validator->getData();
        $checkInTime = $data['check_in_time'] ?? null;
        $closingTime = $data['closing_time'] ?? null;

        // どちらか片方でも未入力の場合は、このルールではエラーにしない
        if (empty($checkInTime) || empty($closingTime)) {
            return true;
        }

        // 日付フォーマットのバリデーションは別途行われると仮定
        try {
            $checkIn = Carbon::parse($checkInTime);
            $closing = Carbon::parse($closingTime);
        } catch (\Exception $e) {
            return true;
        }

        // 出勤時間が退勤時間より前であること
        return $checkIn->lt($closing);
    });

        // カスタムエラーメッセージ（messagesメソッドで上書き可能）
        Validator::replacer('check_in_and_closing_times_valid', function ($message, $attribute, $rule, $parameters) {
        return '出勤時間もしくは退勤時間が不適切な値です';
    });

    Validator::extend('break_start_time_valid', function ($attribute, $value, $parameters, $validator) {
        $data = $validator->getData();
        $parts = explode('.', $attribute);
        array_pop($parts);
        $basePath = implode('.', $parts);

        $closingTime = $data['closing_time'] ?? null;
$closing = $closingTime ? Carbon::parse($closingTime) : null;
        $checkInTime = $data['check_in_time'] ?? null;
        $endTime = data_get($data, $basePath . '.end_time');

        if (empty($value)) {
            return true; // 休憩開始時間が空の場合はこのルールではチェックしない
        }

        try {
            $breakStartTime = Carbon::parse($value);
            $checkIn = $checkInTime ? Carbon::parse($checkInTime) : null;
            $breakEndTime = $endTime ? Carbon::parse($endTime) : null;
        } catch (\Exception $e) {
            return true;
        }

        //休憩開始時間が出勤時間より後であること (出勤時間がある場合)
        if ($checkIn && $breakStartTime->lt($checkIn)) {
            return false;
        }

        //休憩開始時間が休憩終了時間より前であること (休憩終了時間がある場合)
        if ($breakEndTime && $breakStartTime->gte($breakEndTime)) { // 等しい場合もNG
            return false;
        }

        return true;
    });

    // 休憩終了時間
    Validator::extend('break_end_time_valid', function ($attribute, $value, $parameters, $validator) {
        $data = $validator->getData();

        $parts = explode('.', $attribute);
        array_pop($parts);
        $basePath = implode('.', $parts);

        $closingTime = $data['closing_time'] ?? null;
        $startTime = data_get($data, $basePath . '.start_time'); // breaks.X.start_time または new_breaks.X.start_time

        if (empty($value)) {
            return true; // 休憩終了時間が空の場合はこのルールではチェックしない
        }

        try {
            $breakEndTime = Carbon::parse($value);
            $breakStartTime = $startTime ? Carbon::parse($startTime) : null;
            $closing = $closingTime ? Carbon::parse($closingTime) : null;
        } catch (\Exception $e) {
            return true;
        }

        if ($breakStartTime && $breakEndTime->lte($breakStartTime)) {
            return false;
        }

        if ($closing && $breakEndTime->gt($closing)) {
            return false;
        }

        return true;
    });

    }
}
