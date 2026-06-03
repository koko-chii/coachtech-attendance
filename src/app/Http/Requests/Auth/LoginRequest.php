<?php

namespace App\Http\Requests\Auth;

//laravelが用意したFormRequest(入力チェック)機能を使うために呼び出す
use Illuminate\Foundation\Http\FormRequest;

//FormRequest機能を継承したLoginRequest(オリジナルログインチェック機能)を
// 作成するためのクラス(設置)
class LoginRequest extends FormRequest
{
    //入力チェックの許可を判定するための関数(機能)
    public function authorize(): bool
    {
        //誰でも許可します
        return true;
    }

    //入力チェックのルールを決めるための関数(機能)
    public function rules(): array
    {
        //メールアドレスは入力必須、文字列、メール形式であること
        //パスワードは入力必須、文字列であること
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    //エラーメッセージを決めるための関数(機能)
    public function messages(): array
    {
        //メールアドレスとパスワードがが未入力の場合
        return [
            // 1. 未入力の場合
            'email.required' => 'メールアドレスを入力してください',
            'password.required' => 'パスワードを入力してください',

            // メール形式ではない場合（仕様書にログイン時の指定はなし)
            'email.email' => 'メールアドレスはメール形式で入力してください',
        ];
    }
}
