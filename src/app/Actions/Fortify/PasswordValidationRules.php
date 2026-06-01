<?php

namespace App\Actions\Fortify;

//オリジナルバリデーションルールを作成するための呼び出し
use Illuminate\Contracts\Validation\Rule;
//パスワードのオリジナルバリデーションルールを作成するための呼び出し
use Illuminate\Validation\Rules\Password;

//パスワードのバリデーションルールを色々なファイルで共通に使えるトレイト(部品)を作成
trait PasswordValidationRules
{
    //パスワードのルールを定義する関数(箱)
    protected function passwordRules(): array
    {
        //パスワードの(入力必須、文字列、８文字以上、確認用パスワードとの一致)ルールを組合わせて用意しておく
        return ['required', 'string', Password::default(), 'confirmed'];
    }
}
