<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
//正確な時間機能の読み込み
use Carbon\Carbon;

class UpdateAttendanceRequest extends FormRequest
{
   //このリクエストの実行権限を全てのユーザーに許可
    public function authorize(): bool
    {
        return true;
    }

    //ルールを決めるための関数(機能)
    //(出勤時刻の入力必須、退勤時刻入力必須・休憩データ取得、・備考入力必須)
    public function rules(): array
    {
        return [
            'clock_in'             => ['required'],
            'clock_out'            => ['required'],
            'breaks'               => ['nullable', 'array'],
            'remarks'              => ['required'],
        ];
    }

    //メッセージを表示するための機能
    public function messages(): array
    {
        return [
            'clock_in.required'                 => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.required'                => '出勤時間もしくは退勤時間が不適切な値です',
            'remarks.required'                  => '備考を記入してください',
        ];
    }

    //データの検証が完了した後に、出退勤・休憩時間と新規休憩時間の不適切な値をチェック
    protected function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            //「H:i」の時間形式に統一して比較できるように変換
            $clockIn = $this->filled('clock_in') ? Carbon::parse($this->input('clock_in'))->format('H:i') : null;
            $clockOut = $this->filled('clock_out') ? Carbon::parse($this->input('clock_out'))->format('H:i') : null;

            // 出勤時間が退勤時間より後、退勤時間が出勤時間より前の場合
            if (filled($clockIn) && filled($clockOut)) {
                if ($clockIn > $clockOut) {
                    $validator->errors()->add('clock_out', '出勤時間もしくは退勤時間が不適切な値です');
                }
            }

            // 既存の休憩1のチェック
            if ($this->has('breaks')) {
                foreach ($this->input('breaks') as $index => $breakData) {
                    //休憩時間も「H:i」に変換
                    $breakIn = filled($breakData['break_in'] ?? null) ? Carbon::parse($breakData['break_in'])->format('H:i') : null;
                    $breakOut = filled($breakData['break_out'] ?? null) ? Carbon::parse($breakData['break_out'])->format('H:i') : null;

                    if (blank($breakIn)) {
                        $validator->errors()->add('breaks.' . $index . '.break_in', '休憩時間が不適切な値です');
                    }
                    if (blank($breakOut)) {
                        $validator->errors()->add('breaks.' . $index . '.break_out', '休憩時間もしくは退勤時間が不適切な値です');
                    }

                    if (filled($breakIn) && filled($breakOut)) {
                        // 休憩終了時間が退勤時間より後になっている場合、開始が終了より後のとき
                        if ((filled($clockOut) && $breakOut > $clockOut) || $breakIn > $breakOut) {
                            $validator->errors()->add('breaks.' . $index . '.break_out', '休憩時間もしくは退勤時間が不適切な値です');
                        } 
                        // 休憩開始時間が出勤時間より前、退勤時間より後になっている場合
                        else {
                            if ((filled($clockIn) && $breakIn < $clockIn) || (filled($clockOut) && $breakIn > $clockOut)) {
                                $validator->errors()->add('breaks.' . $index . '.break_in', '休憩時間が不適切な値です');
                            }
                        }
                    }
                }
            }

            // 追加休憩の未入力と、不適切のチェック
            $newBreakIn = $this->filled('new_break_in') ? Carbon::parse($this->input('new_break_in'))->format('H:i') : null;
            $newBreakOut = $this->filled('new_break_out') ? Carbon::parse($this->input('new_break_out'))->format('H:i') : null;

            // 追加休憩は片方だけ入力されている時に不適切エラー
            if (filled($newBreakIn) || filled($newBreakOut)) {
                if (blank($newBreakIn)) {
                    $validator->errors()->add('new_break_in', '休憩時間が不適切な値です');
                }
                if (blank($newBreakOut)) {
                    $validator->errors()->add('new_break_out', '休憩時間もしくは退勤時間が不適切な値です');
                }
                
                // 両方入っている時は時間の前後チェック
                if (filled($newBreakIn) && filled($newBreakOut)) {
                    // 休憩開始時間が休憩終了時間より後になっている場合、終了が退勤より後のとき
                    if ($newBreakIn > $newBreakOut || (filled($clockOut) && $newBreakOut > $clockOut)) {
                        $validator->errors()->add('new_break_out', '休憩時間もしくは退勤時間が不適切な値です');
                    }

                    else {
                        if ((filled($clockIn) && $newBreakIn < $clockIn) || (filled($clockOut) && $newBreakIn > $clockOut)) {
                            $validator->errors()->add('new_break_in', '休憩時間が不適切な値です');
                        }
                    }
                }
            }
        });
    }
}
