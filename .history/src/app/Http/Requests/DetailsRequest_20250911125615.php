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

            'breaks.*.start_time.after_or_equal' => '休憩時間が不適切な値です',
            'breaks.*.start_time.before' => '休憩時間が不適切な値です',
            'breaks.*.end_time.before_or_equal' => '休憩時間もしくは退勤時間が不適切な値です',
            'breaks.*.end_time.after' => '休憩時間が不適切な値です',

            'new_breaks.*.start_time.after_or_equal' => '休憩時間が不適切な値です',
            'new_breaks.*.start_time.before' => '休憩時間が不適切な値です',
            'new_breaks.*.end_time.before_or_equal' => '休憩時間もしくは退勤時間が不適切な値です',
            'new_breaks.*.end_time.after' => '休憩時間が不適切な値です',

            'remarks.required' => '備考を記入してください',
        ];
    }
}
