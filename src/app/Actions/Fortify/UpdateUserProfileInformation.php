<?php

namespace App\Actions\Fortify;

use App\Models\User;
//ユーザーのメールアドレスの確認機能を呼び出す
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
//バリデーションルールの呼び出し
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
//プロフィール更新ルールを呼出す
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

//プロフィール更新ルールを実装するクラス(設置)
class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    //プロフィールを更新するための関数(機能)
    public function update(User $user, array $input): void
    {
        // 画面から直接データが届かない場所のためRequestファイルは動かせない。
        //プロフィールの更新ルールを呼出し(名前入力必須、文字列、255文字以内)
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],

            //(メールアドレス入力必須、文字列、メール形式、255字以内、
            // 他ユーザーとの重複不可だけど自分のアドレスは許可)
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            // これらのルールを組み合わせてプロフィール更新のバリデーションを行う
        ])->validateWithBag('updateProfileInformation');

        // メール認証通過しなければエラーメッセージの表示
        // 通過できれば名前とメールアドレスを更新保存
        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'name' => $input['name'],
                'email' => $input['email'],
            ])->save();
        }
    }

    //プロフィール更新ルールを行うための関数(機能)
    protected function updateVerifiedUser(User $user, array $input): void
    {
        //新しい名前とメールアドレスを保存し、メール認証をリセットする。
        $user->forceFill([
            'name' => $input['name'],
            'email' => $input['email'],
            'email_verified_at' => null,
        ])->save();

        //新しいメールアドレスに認証メールを送る
        $user->sendEmailVerificationNotification();
    }
}
