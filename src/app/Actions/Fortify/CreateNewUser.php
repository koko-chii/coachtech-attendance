<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Http\Requests\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    /**
     * 会員登録の処理を行うメインメソッド
     */
    public function create(array $input): User
    {
        // 💡 独自に作成した RegisterRequest のバリデーションルールを手動で実行します
        $request = new \App\Http\Requests\Auth\RegisterRequest();

        // データの検証を実行（ルール違反があれば自動的に画面へ弾かれます）
        \Illuminate\Support\Facades\Validator::make($input, $request->rules(), $request->messages())->validate();

        // バリデーションを通過した場合、ユーザーをデータベース（usersテーブル）に登録
        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }
}
