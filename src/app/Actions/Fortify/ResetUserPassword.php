<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

// パスワードをリセットのルールを実装するクラス
class ResetUserPassword implements ResetsUserPasswords
{
    // パスワードルールを共有するトレイト
    use PasswordValidationRules;

    /**
     * ユーザーのパスワードをリセットする
     *
     * @param User $user パスワードを変更するユーザーのデータ
     * @param array $input 新しいパスワードが入っている箱
     * @return void 戻り値なし
     */
    public function reset(User $user, array $input): void
    {
       // 画面から直接データが届かない場所のためRequestファイルは動かせない。
       // 共通パスワードルールを使ってバリデーションを行う
        Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->validate();

        // パスワードを暗号化して保存する
        $user->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();
    }
}
