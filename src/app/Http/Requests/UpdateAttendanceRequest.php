<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clock_in'  => ['required'],
            'clock_out' => ['required'],
            'breaks'    => ['nullable', 'array'],
            'remarks'   => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'clock_in.required'  => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.required' => '出勤時間もしくは退勤時間が不適切な値です',
            'remarks.required'   => '備考を記入してください',
        ];
    }

    protected function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $clockIn  = $this->filled('clock_in') ? Carbon::parse($this->input('clock_in'))->format('H:i') : null;
            $clockOut = $this->filled('clock_out') ? Carbon::parse($this->input('clock_out'))->format('H:i') : null;

            if (filled($clockIn) && filled($clockOut)) {
                if ($clockIn > $clockOut) {
                    $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
                    return;
                }
            }

            if ($this->has('breaks')) {
                foreach ($this->input('breaks') as $index => $breakData) {
                    $breakIn  = filled($breakData['break_in'] ?? null) ? Carbon::parse($breakData['break_in'])->format('H:i') : null;
                    $breakOut = filled($breakData['break_out'] ?? null) ? Carbon::parse($breakData['break_out'])->format('H:i') : null;

                    if (filled($breakIn)) {
                        if ((filled($clockIn) && $breakIn < $clockIn) || (filled($clockOut) && $breakIn > $clockOut)) {
                            $validator->errors()->add('breaks.' . $index . '.break_in', '休憩時間が不適切な値です');
                            continue;
                        }
                    }

                    if (filled($breakOut)) {
                        if ((filled($clockOut) && $breakOut > $clockOut) || (filled($breakIn) && $breakIn > $breakOut)) {
                            $validator->errors()->add('breaks.' . $index . '.break_out', '休憩時間もしくは退勤時間が不適切な値です');
                        }
                    }
                }
            }

            $newBreakIn  = $this->filled('new_break_in') ? Carbon::parse($this->input('new_break_in'))->format('H:i') : null;
            $newBreakOut = $this->filled('new_break_out') ? Carbon::parse($this->input('new_break_out'))->format('H:i') : null;

            if (filled($newBreakIn)) {
                if ((filled($clockIn) && $newBreakIn < $clockIn) || (filled($clockOut) && $newBreakIn > $clockOut)) {
                    $validator->errors()->add('new_break_in', '休憩時間が不適切な値です');
                    return;
                }
            }

            if (filled($newBreakOut)) {
                if ((filled($clockOut) && $newBreakOut > $clockOut) || (filled($newBreakIn) && $newBreakIn > $newBreakOut)) {
                    $validator->errors()->add('new_break_out', '休憩時間もしくは退勤時間が不適切な値です');
                }
            }
        });
    }
}
