<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TimeRequest extends FormRequest
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
    public function rules(){
        return [
            // 勤怠
            'attendance_start_at' => [
                'required',
                'date_format:H:i',
            ],
            'attendance_finish_at' => [
                'nullable',
                'date_format:H:i',
                'after:attendance_start_at',
            ],
            // 休憩
            'breaks.*.start' => [
                'nullable',
                'date_format:H:i',
                'after:attendance_start_at',
                'before:attendance_finish_at',
            ],
            'breaks.*.finish' => [
                'nullable',
                'date_format:H:i',
                'after:breaks.*.start',
                'before:attendance_finish_at',
            ],

            'remarks' => 'required',
        ];
    }

    public function messages(){
        return [
            'attendance_start_at.required' => '開始時間を入力してください',
            'attendance_start_at.date_format' => '開始時間は HH:mm で入力してください',
            'attendance_finish_at.date_format' => '終了時間は HH:mm で入力してください',
            'attendance_finish_at.after' => '出勤時間もしくは退勤時間が不適切な値です',

            'breaks.*.start.date_format' => '終了時間は HH:mm で入力してください',
            'breaks.*.start.after' => '休憩時間が不適切な値です',
            'breaks.*.start.before' => '休憩時間が不適切な値です',
            'breaks.*.finish.date_format' => '終了時間は HH:mm で入力してください',
            'breaks.*.finish.after' => '休憩時間が不適切な値です',
            'breaks.*.finish.before' => '休憩時間もしくは退勤時間が不適切な値です',

            'remarks.required' => '備考を記入してください',
        ];
    }
}
