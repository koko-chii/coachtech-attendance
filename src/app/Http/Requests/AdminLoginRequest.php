<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

//laravelのバリデーションを継承したクラス
class AdminLoginRequest extends FormRequest
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
        //メール形式のメールアドレスとパスワード必須
        return [
            'email' => 'required|email',
            'password' => 'required',
        ];
    }

    //バリデーションメッセージを定義
    public function messages(): array
    {      
        return [
            'email.required' => 'メールアドレスを入力してください',
            'email.email' => '有効なメールアドレス形式で入力してください',
            'password.required' => 'パスワードを入力してください',
        ];
    }
}
