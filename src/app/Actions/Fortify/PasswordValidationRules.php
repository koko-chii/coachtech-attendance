<?php

namespace App\Actions\Fortify;

// パスワードのオリジナルバリデーションルールを作成するための呼び出し
use Illuminate\Validation\Rules\Password;

// パスワードのバリデーションルールを色々なファイルで共通に使えるトレイト(部品)を作成
trait PasswordValidationRules
{
    /**
     * パスワードのバリデーションルールを定義
     *
     * @return array パスワードルールの配列
     */
    protected function passwordRules(): array
    {
        // パスワードの入力必須、文字列、８文字以上、確認用パスワードとの一致
        return ['required', 'string', Password::default(), 'confirmed'];
    }
}
