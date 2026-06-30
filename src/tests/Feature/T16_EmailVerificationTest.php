<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use PHPUnit\Framework\Attributes\Test;

class T16_EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function 会員登録後_認証メールが送信される(): void
    {
        Notification::fake();

        $userData = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->post('/register', $userData); 

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    #[Test]
    public function メール認証誘導画面で認証はこちらからボタンを押下するとメール認証サイトに遷移する(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user)->get('/email/verify'); 
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/email/go-to-mailpit');
        $response->assertStatus(302);
    }

    #[Test]
    public function メール認証サイトのメール認証を完了すると_勤怠登録画面に遷移する(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect(route('attendance.index', ['verified' => 1]));
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }
}
