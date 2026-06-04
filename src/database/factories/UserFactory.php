<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

//laravel標準装備のFactory機能を継承したオリジナルのダミーデーターを作成するためのクラス(設置)
class UserFactory extends Factory
{
    //パスワードの暗号化処理を、一時的に保存しておく変数(箱)
    protected static ?string $password;

    //ダミーデーターを作成するための関数(機能)
    public function definition(): array
    {
        //ダミーデータには、名前とメール認証したメールアドレス、
        // 暗号化したパスワード、ログイン保持トークン(ランダム英数字10字)を定義する
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    //メール未認証のダミーデーターを作成するための関数(機能)
    public function unverified(): static
    {
        //メール認証日時を空っぽに書き換える
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
