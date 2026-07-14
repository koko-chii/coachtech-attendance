<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
// 日付・時刻操作(打刻時間や勤務時間計算)をするための読み込み
use Carbon\Carbon;

// laravel標準のバリデーション機能を継承したクラス
class UpdateAttendanceRequest extends FormRequest
{
    /**
     * リクエストの実行権限をチェック
     */
    public function authorize(): bool
    {
        // リクエスト許可
        return true;
    }

    /**
     * バリデーションルールを定義
     */
    public function rules(): array
    {
        // 出勤時刻・退勤時刻・備考は必須、休憩時刻は空欄可、時刻形式チェック
        return [
            'clock_in'           => ['required', 'date_format:H:i'],
            'clock_out'          => ['required', 'date_format:H:i'],
            'comment'            => ['required'],
            'breaks'             => ['nullable', 'array'],
            'breaks.*.break_in'  => ['nullable', 'date_format:H:i'],
            'breaks.*.break_out' => ['nullable', 'date_format:H:i'],
            
            // 新規追加の休憩時間チェック
            'new_break_in'       => ['nullable', 'date_format:H:i'],
            'new_break_out'      => ['nullable', 'date_format:H:i'],
        ];
    }

    /**
     * バリデーションエラーメッセージを定義
     */
    public function messages(): array
    {
        return [
            'clock_in.required'              => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_in.date_format'           => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.required'             => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.date_format'          => '出勤時間もしくは退勤時間が不適切な値です',
            'comment.required'               => '備考を記入してください',

            'breaks.*.break_in.date_format'  => '休憩時間が不適切な値です',
            'breaks.*.break_out.date_format' => '休憩時間もしくは退勤時間が不適切な値です',
            'new_break_in.date_format'       => '休憩時間が不適切な値です',
            'new_break_out.date_format'      => '休憩時間もしくは退勤時間が不適切な値です',
        ];
    }

    /**
     * バリデーション後の追加チェックを設定（出退勤時間と休憩時間の不整合をチェック）
     *
     * @param mixed $validator バリデータインスタンス
     * @return void
     */
    protected function withValidator($validator): void
    {
        // 出勤時刻と退勤時刻、休憩時間の前後関係を追加チェック
        $validator->after(function (Validator $validator): void {

            // 出勤時刻と退勤時刻を時刻形式（H:i）に変換
            $clockIn = $this->filled('clock_in')
                ? Carbon::parse($this->input('clock_in'))->format('H:i')
                : null;

            $clockOut = $this->filled('clock_out')
                ? Carbon::parse($this->input('clock_out'))->format('H:i')
                : null;

            // もし出勤時刻が退勤時刻より遅い場合はエラー
            if (filled($clockIn) && filled($clockOut)) {
                if ($clockIn > $clockOut) {
                    $validator->errors()->add(
                        'clock_in',
                        '出勤時間もしくは退勤時間が不適切な値です'
                    );
                }
            }

            // 複数の休憩入力データを時刻形式(H:i)に変換
            if ($this->has('breaks') && is_array($this->input('breaks'))) {
                collect($this->input('breaks'))->each(function ($breakData, $index) use ($validator, $clockIn, $clockOut) {

                    // 未入力チェック
                    $hasBreakIn  = filled($breakData['break_in'] ?? null);
                    $hasBreakOut = filled($breakData['break_out'] ?? null);

                    // 入力がある場合の時刻形式チェック、未入力の場合は空
                    $breakIn = $hasBreakIn
                        ? Carbon::parse($breakData['break_in'])->format('H:i')
                        : null;

                    $breakOut = $hasBreakOut
                        ? Carbon::parse($breakData['break_out'])->format('H:i')
                        : null;

                    // 1つでも休憩エラーが発生した場合は重複表示を防ぐ
                    $hasError = false;

                    // 休憩開始時刻が出勤前、または退勤後ならエラー
                    if (filled($breakIn)) {
                        if ((filled($clockIn) && $breakIn < $clockIn) ||
                            (filled($clockOut) && $breakIn > $clockOut)) {

                            $validator->errors()->add(
                                'breaks.' . $index . '.break_in',
                                '休憩時間が不適切な値です'
                            );
                            // エラーありの場合
                            $hasError = true;

                            // 次のデータの処理へ進む
                            return;
                        }
                    }

                    // 休憩終了時刻が出勤前、退勤後ならエラー（開始時刻側でエラーがない場合のみチェック）
                    if (!$hasError && filled($breakOut)) {
                        if ((filled($clockIn) && $breakOut < $clockIn) ||
                            (filled($clockOut) && $breakOut > $clockOut)) {

                            $validator->errors()->add(
                                'breaks.' . $index . '.break_out',
                                '休憩時間もしくは退勤時間が不適切な値です'
                            );

                            $hasError = true;
                        }
                    }

                    // 休憩開始 > 休憩終了 の場合エラー（開始・終了時刻側でエラーがない場合のみチェック）
                    if (!$hasError && filled($breakIn) && filled($breakOut) && $breakOut < $breakIn) {
                        $validator->errors()->add(
                            'breaks.' . $index . '.break_out',
                            '休憩時間が不適切な値です'
                        );

                        $hasError = true;
                    }

                    // 他のエラーが一切ない場合のみ、片方未入力をチェックする
                    if (!$hasError && $validator->errors()->isEmpty()) {
                        if ($hasBreakIn && !$hasBreakOut) {
                            $validator->errors()->add('breaks.' . $index . '.break_in', '休憩時間が不適切な値です');
                        }
                    }
                });
            }

            // 追加の休憩入力データを時刻形式(H:i)に変換
            $hasNewBreakIn  = $this->filled('new_break_in');
            $hasNewBreakOut = $this->filled('new_break_out');

            $newBreakIn = $hasNewBreakIn
                ? Carbon::parse($this->input('new_break_in'))->format('H:i')
                : null;

            $newBreakOut = $hasNewBreakOut
                ? Carbon::parse($this->input('new_break_out'))->format('H:i')
                : null;

            // 新規の休憩重複エラー防止フラグ
            $hasNewError = false;

            // 休憩開始時刻が出勤前、または退勤後ならエラー
            if (filled($newBreakIn)) {
                if ((filled($clockIn) && $newBreakIn < $clockIn) ||
                    (filled($clockOut) && $newBreakIn > $clockOut)) {

                    $validator->errors()->add(
                        'new_break_in',
                        '休憩時間が不適切な値です'
                    );

                    $hasNewError = true;

                    return;
                }
            }

            // 休憩終了時刻が出勤前、退勤後ならエラー（新規開始側でエラーがない場合のみチェック）
            if (!$hasNewError && filled($newBreakOut)) {
                if ((filled($clockIn) && $newBreakOut < $clockIn) ||
                    (filled($clockOut) && $newBreakOut > $clockOut)) {

                    $validator->errors()->add(
                        'new_break_out',
                        '休憩時間もしくは退勤時間が不適切な値です'
                    );

                    $hasNewError = true;

                    return;
                }
            }

            // 休憩開始 > 休憩終了 の場合エラー（新規開始・終了側でエラーがない場合のみチェック）
            if (!$hasNewError && filled($newBreakIn) &&
                filled($newBreakOut) &&
                $newBreakOut < $newBreakIn) {

                $validator->errors()->add(
                    'new_break_out',
                    '休憩時間が不適切な値です'
                );

                $hasNewError = true;
            }

            // 他のエラーが一切ない場合のみ、新規の片方未入力をチェックする
            if (!$hasNewError && $validator->errors()->isEmpty()) {
                if ($hasNewBreakIn && !$hasNewBreakOut) {
                    $validator->errors()->add('new_break_in', '休憩時間が不適切な値です');
                }
            }
        });
    }
}

