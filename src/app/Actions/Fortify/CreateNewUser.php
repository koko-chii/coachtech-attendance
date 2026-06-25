<?php

// laravelのfortifyがこのファイルを見つけれるように、このファイルの住所がかいてある
namespace App\Actions\Fortify;

// データーベースのuser情報を扱うためにuserモデルを呼出している
use App\Models\User;
// ユーザーが入力したデータを受取り、バリデーションチェックをしてもらうために呼び出している
use App\Http\Requests\Auth\RegisterRequest;
// パスワードを暗号化するために呼び出している
use Illuminate\Support\Facades\Hash;
// laravelのCreatesNewUsersのルールを呼出しcreateメソッドを実装するよう書いてある
use Laravel\Fortify\Contracts\CreatesNewUsers;
// バリデーションを実行する機能を呼び出している
use Illuminate\Support\Facades\Validator;

// laravelのCreateNewUsersrルールを実装するクラス
class CreateNewUser implements CreatesNewUsers
{
    // ユーザー情報を新規作成するため(データの受取・チェック・保存)を1つにまとめたcreateメソッド
    // public 公開範囲(誰でも自由に) function関数(機能) create(この関数の名前。今回は新規作成)
    public function create(array $input): User
    {
        // ユーザーが入力したデータを受取り、バリデーションチェックをしてもらう
        $request = new RegisterRequest();

        // バリデーションを実行。ルール違反があれば設定したエラーメッセージを表示
        Validator::make($input, $request->rules(), $request->messages())->validate();

        // パスワードは安全に暗号化し、ユーザー情報をデータベース（usersテーブル）に登録
        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }
}
