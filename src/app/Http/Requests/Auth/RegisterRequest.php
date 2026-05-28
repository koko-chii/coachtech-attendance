<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * 🛠️ 1. 誰でもこの入力チェックを通れるように true に変更します
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 🛠️ 2. 要件定義に基づいた入力チェックのルール（FN002）
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * 🛠️ 3. 【最重要】要件定義で必ず守るよう指定されたエラーメッセージ（FN003）
     */
    public function messages(): array
    {
        return [
            // 1. 未入力の場合
            'name.required' => 'お名前を入力してください',
            'email.required' => 'メールアドレスを入力してください',
            'password.required' => 'パスワードを入力してください',

            // 2. メール形式ではない場合
            'email.email' => 'メールアドレスはメール形式で入力してください',

            // 3. パスワードの入力規則違反の場合
            'password.min' => 'パスワードは8文字以上で入力してください',

            // 4. 確認用パスワードの入力規則違反（不一致）の場合
            'password.confirmed' => 'パスワードと一致しません',
        ];
    }
}
