<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceRecord;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;

class T19_SanctumAuthTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function 未認証時に書き込み系APIで401が返る(): void
    {
        $response = $this->postJson('/api/v1/attendance-records', []);
        $response->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);

        $response = $this->putJson('/api/v1/attendance-records/1', []);
        $response->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);

        $response = $this->deleteJson('/api/v1/attendance-records/1');
        $response->assertStatus(401)->assertJson(['message' => 'Unauthenticated.']);
    }

    #[Test]
    public function 認証済みユーザーは自分の勤怠を更新または削除できる(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $record = AttendanceRecord::factory()->create(['user_id' => $user->id, 'date' => '2026-06-24']);

        $putResponse = $this->putJson("/api/v1/attendance-records/{$record->id}", [
            'date' => '2026-06-24',
            'clock_in' => '10:00:00'
        ]);
        $putResponse->assertStatus(200);

        $deleteResponse = $this->deleteJson("/api/v1/attendance-records/{$record->id}");
        $deleteResponse->assertStatus(204);
    }

    #[Test]
    public function 他ユーザーの勤怠を更新または削除しようとすると403が返る(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        Sanctum::actingAs($user);
        $record = AttendanceRecord::factory()->create(['user_id' => $otherUser->id]);

        $putResponse = $this->putJson("/api/v1/attendance-records/{$record->id}", [
            'date' => '2026-06-24',
            'clock_in' => '09:00:00'
        ]);
        $putResponse->assertStatus(403)->assertJson(['error' => 'この操作を実行する権限がありません。']);

        $deleteResponse = $this->deleteJson("/api/v1/attendance-records/{$record->id}");
        $deleteResponse->assertStatus(403)->assertJson(['error' => 'この操作を実行する権限がありません。']);
    }
}

