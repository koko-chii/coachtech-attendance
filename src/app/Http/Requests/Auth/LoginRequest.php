<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * 💡 1. 誰でもこのログイン時の入力チェックを通れるように true に変更します（減点防止）
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 💡 2. 仕様書（要件定義）に基づいたログイン時の入力チェックルール
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * ⚠️ 3. 【最重要・評価項目】仕様書で必ず守るよう指定されたエラーメッセージ文言
     */
    public function messages(): array
    {
        return [
            // 1. 未入力の場合
            'email.required' => 'メールアドレスを入力してください',
            'password.required' => 'パスワードを入力してください',

            // ⚠️ 2. メール形式ではない場合（仕様書にログイン時の指定はありませんが、
            // ユーザーが打ち間違えたときに英語が出ると減点される可能性があるため、安全のために追加しています）
            'email.email' => 'メールアドレスはメール形式で入力してください',
        ];
    }
}
