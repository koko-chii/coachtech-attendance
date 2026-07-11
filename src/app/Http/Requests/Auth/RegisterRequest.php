<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

// FormRequest機能を継承したオリジナル会員登録機能を作成するためのクラス(設置)
class RegisterRequest extends FormRequest
{
    /**
     *入力チェックの許可を判定するための関数(機能)
     */
    public function authorize(): bool
    {
        // 誰でも許可する
        return true;
    }

    /**
     * 入力チェックのルールをきめるための関数(機能)
     */
    public function rules(): array
    {
        // 名前の入力必須、文字列、255字以内
        // メールアドレスの入力必須、文字列、メール形式、255字以内、未登録のアドレス
        // パスワードの入力必須、文字列、8字以内、確認用パスワードとの一致
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * エラーメッセージを決めるための関数(機能)
     */
    public function messages(): array
    {
        // 名前とメールアドレスと、パスワードが未入力の場合
        return [
            'name.required' => 'お名前を入力してください',
            'email.required' => 'メールアドレスを入力してください',
            'password.required' => 'パスワードを入力してください',

            // メール形式ではない場合
            'email.email' => 'メールアドレスはメール形式で入力してください',

            // すでに登録されているメールアドレスの場合
            'email.unique' => 'このメールアドレスは既に登録されています。',

            // パスワードの入力数が7字以下の場合
            'password.min' => 'パスワードは8文字以上で入力してください',

            // 確認用パスワードと不一致の場合
            'password.confirmed' => 'パスワードと一致しません',
        ];
    }
}
