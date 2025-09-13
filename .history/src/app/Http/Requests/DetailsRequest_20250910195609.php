<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
                Rule::when($this->filled('closing_time'), ['before:closing_time']),
                //closing_timeが入力されている場合のみ
                //check_in_timeが
            ],
            'closing_time' => [
                'required',
                'date_format:H:i',
                Rule::when($this->filled('check_in_time'), ['after:check_in_time']),
            ],
            'breaks.*.start_time' => [
                'nullable',
                'date_format:H:i',
                Rule::when($this->filled('check_in_time'), ['after_or_equal:check_in_time']),
                Rule::when($this->filled('breaks.*.end_time'), ['before:breaks.*.end_time']),
            ],
            'breaks.*.end_time' => [
                'nullable',
                'date_format:H:i',
                Rule::when($this->filled('closing_time'), ['before_or_equal:closing_time']),
                Rule::when($this->filled('breaks.*.start_time'), ['after:breaks.*.start_time']),
            ],
            'new_breaks.*.start_time' => [
                'nullable',
                'date_format:H:i',
                Rule::when($this->filled('check_in_time'), ['after_or_equal:check_in_time']),
                Rule::when($this->filled('new_breaks.*.end_time'), ['before:new_breaks.*.end_time']),
            ],
            'new_breaks.*.end_time' => [
                'nullable',
                'date_format:H:i',
                Rule::when($this->filled('closing_time'), ['before_or_equal:closing_time']),
                Rule::when($this->filled('new_breaks.*.start_time'), ['after:new_breaks.*.start_time']),
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
            'check_in_time.before' => '出勤時間もしくは退勤時間が不適切な値です',
            'closing_time.after' => '出勤時間もしくは退勤時間が不適切な値です',

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
