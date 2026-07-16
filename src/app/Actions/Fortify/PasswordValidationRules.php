<?php

namespace App\Actions\Fortify;

use Illuminate\Validation\Rules\Password;

// パスワードのバリデーションルールを共有するためのトレイト(共通機能をまとめた部品)
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
