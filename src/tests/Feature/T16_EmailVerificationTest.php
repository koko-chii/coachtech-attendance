<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use PHPUnit\Framework\Attributes\Test;

class T16_EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function 会員登録後に認証メールが送信される(): void
    {
        Event::fake();

        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        Event::assertDispatched(Registered::class);
    }
}

