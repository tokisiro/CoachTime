<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DetailsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'check_in_time' => [
                'required',
                'date_format:H:i',
                'check_in_and_closing_times_valid',
            ],
            'closing_time' => [
                'required',
                'date_format:H:i',
            ],
            'breaks.*.start_time' => [
                'nullable',
                'date_format:H:i',
                'break_start_time_valid',
            ],
            'breaks.*.end_time' => [
                'nullable',
                'date_format:H:i',
                'break_end_time_valid',
            ],
            'new_breaks.*.start_time' => [
                'nullable',
                'date_format:H:i',
                'break_start_time_valid',
            ],
            'new_breaks.*.end_time' => [
                'nullable',
                'date_format:H:i',
                'break_end_time_valid',
            ],
            'remarks' => 'required',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'check_in_time.check_in_and_closing_times_valid' => '出勤時間もしくは退勤時間が不適切な値です',

            // 既存の休憩時間の開始時間に関するメッセージ
            // 休憩開始時間が出勤時間より前、または休憩終了時間より後になっている場合
            'breaks.*.start_time.break_start_time_valid' => '休憩時間が不適切な値です',
            'new_breaks.*.start_time.break_start_time_valid' => '休憩時間が不適切な値です',

            // 休憩終了時間が休憩開始時間より前、または退勤時間より後になっている場合
            // ここで、退勤時間より後になっている場合とそれ以外のメッセージを分ける
            'breaks.*.end_time.break_end_time_valid' => '休憩時間もしくは退勤時間が不適切な値です',
            'new_breaks.*.end_time.break_end_time_valid' => '休憩時間もしくは退勤時間が不適切な値です',

            'remarks.required' => '備考を記入してください',
        ];
    }
}
