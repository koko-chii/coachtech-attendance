<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
//バリデーションの呼び出し
use Illuminate\Support\Facades\Validator;
//バリデーションエラー機能の呼び出し
use Illuminate\Validation\ValidationException;
//パスワードをリセットするためのルールを呼び出し
use Laravel\Fortify\Contracts\ResetsUserPasswords;

//パスワードをリセットする為のルールを実装するクラス(設置)
class ResetUserPassword implements ResetsUserPasswords
{
    //PassWordValidationRulesで作成したトレイト(パスワードルール)を呼出す
    use PasswordValidationRules;

    //ユーザーのパスワードをリセットするための関数(機能)
    public function reset(User $user, array $input): void
    {
       // 画面から直接データが届かない場所のためRequestファイルは動かせない。
       //パスワードリセットのルールを呼出しバリデーションを行う
        Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->validate();

        //パスワードを暗号化して保存する
        $user->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();
    }
}
