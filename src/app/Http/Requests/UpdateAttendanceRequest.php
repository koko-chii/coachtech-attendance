<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class UpdateAttendanceRequest extends FormRequest
{
    // 全てのユーザーに実行権限を許可
    public function authorize(): bool
    {
        return true;
    }

    // 基本的なバリデーションルール
    public function rules(): array
    {
        return [
            'clock_in'  => ['required'],
            'clock_out' => ['required'],
            'breaks'    => ['nullable', 'array'],
            'remarks'   => ['required'],
        ];
    }

    // 基本ルールのエラーメッセージ
    public function messages(): array
    {
        return [
            'clock_in.required'  => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.required' => '出勤時間もしくは退勤時間が不適切な値です',
            'remarks.required'   => '備考を記入してください',
        ];
    }

    // 相関チェック（時間の前後関係など）
    protected function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // 「H:i」の形式に統一して比較できるように変換
            $clockIn  = $this->filled('clock_in') ? Carbon::parse($this->input('clock_in'))->format('H:i') : null;
            $clockOut = $this->filled('clock_out') ? Carbon::parse($this->input('clock_out'))->format('H:i') : null;

            // 1. 出勤時間と退勤時間の前後チェック
            if (filled($clockIn) && filled($clockOut)) {
                if ($clockIn > $clockOut) {
                    $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
                }
            }

            // 2. 既存の休憩（配列）のチェック
            if ($this->has('breaks')) {
                foreach ($this->input('breaks') as $index => $breakData) {
                    $breakIn  = filled($breakData['break_in'] ?? null) ? Carbon::parse($breakData['break_in'])->format('H:i') : null;
                    $breakOut = filled($breakData['break_out'] ?? null) ? Carbon::parse($breakData['break_out'])->format('H:i') : null;

                    // 未入力チェック
                    if (blank($breakIn)) {
                        $validator->errors()->add('breaks.' . $index . '.break_in', '休憩時間が不適切な値です');
                    }
                    if (blank($breakOut)) {
                        $validator->errors()->add('breaks.' . $index . '.break_out', '休憩時間もしくは退勤時間が不適切な値です');
                    }

                    // 時間の整合性チェック
                    if (filled($breakIn) && filled($breakOut)) {
                        // 休憩開始時間のチェック（出勤前、または退勤後）
                        if ((filled($clockIn) && $breakIn < $clockIn) || (filled($clockOut) && $breakIn > $clockOut)) {
                            $validator->errors()->add('breaks.' . $index . '.break_in', '休憩時間が不適切な値です');
                        }
                        
                        // 休憩終了時間のチェック（退勤後、または開始より前）
                        if ((filled($clockOut) && $breakOut > $clockOut) || $breakIn > $breakOut) {
                            $validator->errors()->add('breaks.' . $index . '.break_out', '休憩時間もしくは退勤時間が不適切な値です');
                        }
                    }
                }
            }

            // 3. 追加休憩のチェック
            $newBreakIn  = $this->filled('new_break_in') ? Carbon::parse($this->input('new_break_in'))->format('H:i') : null;
            $newBreakOut = $this->filled('new_break_out') ? Carbon::parse($this->input('new_break_out'))->format('H:i') : null;

            if (filled($newBreakIn) || filled($newBreakOut)) {
                // 片方のみ入力されている場合のチェック
                if (blank($newBreakIn)) {
                    $validator->errors()->add('new_break_in', '休憩時間が不適切な値です');
                }
                if (blank($newBreakOut)) {
                    $validator->errors()->add('new_break_out', '休憩時間もしくは退勤時間が不適切な値です');
                }
                
                // 両方入力されている場合の整合性チェック
                if (filled($newBreakIn) && filled($newBreakOut)) {
                    // 休憩開始時間のチェック（出勤前、または退勤後、または終了より後）
                    if ((filled($clockIn) && $newBreakIn < $clockIn) || (filled($clockOut) && $newBreakIn > $clockOut) || $newBreakIn > $newBreakOut) {
                        $validator->errors()->add('new_break_in', '休憩時間が不適切な値です');
                    }

                    // 休憩終了時間のチェック（退勤後）
                    if (filled($clockOut) && $newBreakOut > $clockOut) {
                        $validator->errors()->add('new_break_out', '休憩時間もしくは退勤時間が不適切な値です');
                    }
                }
            }
        });
    }
}
