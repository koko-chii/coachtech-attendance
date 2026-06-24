<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;

class T20_AttendanceReportTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function ゲストはレポートページにアクセスできずログインにリダイレクトされる(): void
    {
        $response = $this->get('/attendance/report');

        $response->assertRedirect('/login');
    }

    #[Test]
    public function 認証ユーザーの統計情報が正しく計算されて画面に渡される(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user);

        $response = $this->get('/attendance/report');

        $response->assertStatus(200)
            ->assertViewHasAll(['summary', 'monthlyData', 'anomaly']);
    }

    #[Test]
    public function 勤怠記録がないユーザーでも安全に処理されてエラーが発生しない(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user);

        $response = $this->get('/attendance/report');

        $response->assertStatus(200);

        $summary = $response->viewData('summary');
        $this->assertEquals(0, $summary['total_work']['h']);
        $this->assertEquals(0, $summary['total_work']['m']);
    }
}
