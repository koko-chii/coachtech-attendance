<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
// パスワードを更新するためのルールを呼び出し
use Laravel\Fortify\Contracts\UpdatesUserPasswords;

// パスワードを更新するためのルールを実装するクラス(設置)
class UpdateUserPassword implements UpdatesUserPasswords
{
    // passwordのルールを定義するトレイト(部品)を呼び出す
    use PasswordValidationRules;

    // ユーザーのパスワードを更新するための関数(機能)
    public function update(User $user, array $input): void
    {
        //  画面から直接データが届かない場所のためRequestファイルは動かせない。
        // パスワード更新のルールを呼出し(パスワードの入力必須、文字列、現在のパスワードと一致)の
        // パスワードルールを組み合わせてバリデーションを行う
        Validator::make($input, [
            'current_password' => ['required', 'string', 'current_password:web'],
            'password' => $this->passwordRules(),
        ], [
            // 現在のパスワードと一致しない場合、「入力されたパスワードが現在のパスワードと一致しません」というエラーメッセージを返す
            'current_password.current_password' => __('The provided password does not match your current password.'),
        ])->validateWithBag('updatePassword');

        // 致した場合はパスワードを暗号化して保存する
        $user->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();
    }
}
