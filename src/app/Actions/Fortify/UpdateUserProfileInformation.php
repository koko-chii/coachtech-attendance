<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

// プロフィール更新ルールを実装するクラス
class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * ユーザーのプロフィール情報を更新する
     *
     * @param User $user プロフィールを変更するユーザーのデータ
     * @param array $input 新しい名前やメールアドレスが入っている箱
     * @return void 戻り値なし
     */
    public function update(User $user, array $input): void
    {
        // Requestファイルを利用できないため、プロフィール更新ルールを作成
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                // 他ユーザーとのメールアドレス重複不可、自分のメールアドレスは許可
                Rule::unique('users')->ignore($user->id),
            ],
        // プロフィール更新フォーム用のエラーとしてバリデーション実行
        ])->validateWithBag('updateProfileInformation');

        // メールアドレス変更するユーザーがメール認証必要な場合、認証状態をリセット
        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
         // メール認証のリセットが不要な場合、そのままプロフィール情報を更新
        } else {
            $user->forceFill([
                'name' => $input['name'],
                'email' => $input['email'],
            ])->save();
        }
    }

    /**
     * メール認証が必要なユーザーのプロフィールを更新する
     *
     * @param User $user プロフィールを変更するユーザーのデータ
     * @param array $input 新しい名前やメールアドレスが入っている箱
     * @return void 戻り値なし
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        // 名前とメールアドレスを更新し、メール認証状態をリセット
        $user->forceFill([
            'name' => $input['name'],
            'email' => $input['email'],
            'email_verified_at' => null,
        ])->save();

        // 新しいメールアドレスに認証メールを送る
        $user->sendEmailVerificationNotification();
    }
}
