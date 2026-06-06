<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;

class T2_UserLoginTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function 一般ユーザー_メールアドレスが未入力の場合バリデーションメッセージが表示される()
    {
        User::factory()->create(['email' => 'user@example.com', 'password' => Hash::make('password123')]);

        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    #[Test]
    public function 一般ユーザー_パスワードが未入力の場合バリデーションメッセージが表示される()
    {
        User::factory()->create(['email' => 'user@example.com', 'password' => Hash::make('password123')]);

        $response = $this->post('/login', [
            'email' => 'user@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    #[Test]
    public function 一般ユーザー_登録内容と一致しない場合バリデーションメッセージが表示される()
    {
        User::factory()->create(['email' => 'user@example.com', 'password' => Hash::make('password123')]);

        $response = $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }
}
