<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
//日付・時刻操作(打刻時間や勤務時間計算)をするための読み込み
use Carbon\Carbon;

//laravel標準のバリデーション機能を継承したクラス
class UpdateAttendanceRequest extends FormRequest
{
    //リクエストの実行権限を判定
    public function authorize(): bool
    {
        //リクエスト許可
        return true;
    }

    //バリデーションルールを定義
    public function rules(): array
    {
        //出勤時刻・退勤時刻・備考は必須、休憩時刻は空欄可
        return [
            'clock_in'  => ['required'],
            'clock_out' => ['required'],
            'breaks'    => ['nullable', 'array'],
            'remarks'   => ['required'],
        ];
    }

    //バリデーションメッセージを定義
    public function messages(): array
    {
        return [
            'clock_in.required'  => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.required' => '出勤時間もしくは退勤時間が不適切な値です',
            'remarks.required'   => '備考を記入してください',
        ];
    }

    //バリデーション後の追加チェックを設定
    protected function withValidator($validator): void
    {
        //出勤時刻と退勤時刻を時刻形式（H:i）に変換
        $validator->after(function ($validator) {
            $clockIn  = $this->filled('clock_in') ? Carbon::parse($this->input('clock_in'))->format('H:i') : null;
            $clockOut = $this->filled('clock_out') ? Carbon::parse($this->input('clock_out'))->format('H:i') : null;

            //もし出勤時刻が退勤時刻より遅い場合はエラー
            if (filled($clockIn) && filled($clockOut)) {
                if ($clockIn > $clockOut) {
                    $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
                    return;
                }
            }

            //複数の休憩入力データを時刻形式(H:i)に変換
            if ($this->has('breaks')) {
                foreach ($this->input('breaks') as $index => $breakData) {
                    $breakIn  = filled($breakData['break_in'] ?? null) ? Carbon::parse($breakData['break_in'])->format('H:i') : null;
                    $breakOut = filled($breakData['break_out'] ?? null) ? Carbon::parse($breakData['break_out'])->format('H:i') : null;

                    //もし休憩開始時刻が出勤前、又は退勤後の場合はエラー
                    if (filled($breakIn)) {
                        if ((filled($clockIn) && $breakIn < $clockIn) || (filled($clockOut) && $breakIn > $clockOut)) {
                            $validator->errors()->add('breaks.' . $index . '.break_in', '休憩時間が不適切な値です');
                            continue;
                        }
                    }

                    //もし休憩終了時刻が退勤後、又は休憩開始より前の場合はエラー
                    if (filled($breakOut)) {
                        if ((filled($clockOut) && $breakOut > $clockOut) || (filled($breakIn) && $breakIn > $breakOut)) {
                            $validator->errors()->add('breaks.' . $index . '.break_out', '休憩時間もしくは退勤時間が不適切な値です');
                        }
                    }
                }
            }

            //追加の休憩入力データを時刻形式(H:i)に変換
            $newBreakIn  = $this->filled('new_break_in') ? Carbon::parse($this->input('new_break_in'))->format('H:i') : null;
            $newBreakOut = $this->filled('new_break_out') ? Carbon::parse($this->input('new_break_out'))->format('H:i') : null;

            //追加休憩開始時刻が出勤前、又は退勤後の場合はエラー
            if (filled($newBreakIn)) {
                if ((filled($clockIn) && $newBreakIn < $clockIn) || (filled($clockOut) && $newBreakIn > $clockOut)) {
                    $validator->errors()->add('new_break_in', '休憩時間が不適切な値です');
                    return;
                }
            }

            //追加休憩終了時刻が退勤後、又は休憩開始より前の場合はエラー
            if (filled($newBreakOut)) {
                if ((filled($clockOut) && $newBreakOut > $clockOut) || (filled($newBreakIn) && $newBreakIn > $newBreakOut)) {
                    $validator->errors()->add('new_break_out', '休憩時間もしくは退勤時間が不適切な値です');
                }
            }
        });
    }
}
