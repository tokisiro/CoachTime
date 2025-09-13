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
        // 出勤時間と退勤時間の関係をチェックするカスタムルール
        Validator::extend('check_in_and_closing_times_valid', function ($attribute, $value, $parameters, $validator) {
        $data = $validator->getData();
        $checkInTime = $data['check_in_time'] ?? null;
        $closingTime = $data['closing_time'] ?? null;

        // どちらか片方でも未入力の場合は、このルールではエラーにしない（requiredなどで別途チェック）
        if (empty($checkInTime) || empty($closingTime)) {
            return true;
        }

        // 日付フォーマットのバリデーションは別途行われると仮定
        try {
            $checkIn = Carbon::parse($checkInTime);
            $closing = Carbon::parse($closingTime);
        } catch (\Exception $e) {
            return true; // パースできない場合はこのルールではエラーにしない
        }

        // 出勤時間が退勤時間より前であること
        return $checkIn->lt($closing);
    });

        // カスタムエラーメッセージ（messagesメソッドで上書き可能）
    Validator::replacer('check_in_and_closing_times_valid', function ($message, $attribute, $rule, $parameters) {
        return '出勤時間と退勤時間の関係が不適切です。';
    });
    }
}
