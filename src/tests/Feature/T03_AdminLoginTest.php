<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;

class T03_AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function 管理者_メールアドレスが未入力の場合バリデーションメッセージが表示される()
    {
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    #[Test]
    public function 管理者_パスワードが未入力の場合バリデーションメッセージが表示される()
    {
        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    #[Test]
    public function 管理者_登録内容と一致しない場合バリデーションメッセージが表示される()
    {
        Admin::create([
            'name' => 'テスト管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'wrong-admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }
}
