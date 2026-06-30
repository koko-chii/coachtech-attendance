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
        $responsePost = $this->postJson('/api/v1/attendance-records', [
            'date' => '2026-06-30',
        ]);
        $responsePost->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);

        $responsePut = $this->putJson('/api/v1/attendance-records/1', [
            'date' => '2026-06-30',
            'clock_in' => '10:00:00',
        ]);
        $responsePut->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);

        $responseDelete = $this->deleteJson('/api/v1/attendance-records/1');
        $responseDelete->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    #[Test]
    public function 認証済みユーザーは自分の勤怠を更新削除できる(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*'], 'sanctum');
        $record = AttendanceRecord::factory()->create(['user_id' => $user->id]);

        $putResponse = $this->putJson("/api/v1/attendance-records/{$record->id}", [
            'date' => $record->date, // ★ 必須項目を追加
            'clock_in' => '10:00:00',
        ]);
        $putResponse->assertStatus(200);

        $deleteResponse = $this->deleteJson("/api/v1/attendance-records/{$record->id}");
        $deleteResponse->assertStatus(204);
    }

    #[Test]
    public function 他ユーザーの勤怠を更新削除しようとすると403が返る(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();
        
        Sanctum::actingAs($userA, ['*'], 'sanctum');
        $recordB = AttendanceRecord::factory()->create(['user_id' => $userB->id]);

        $putResponse = $this->putJson("/api/v1/attendance-records/{$recordB->id}", [
            'date' => $recordB->date, // ★ 必須項目を追加
            'clock_in' => '10:00:00',
        ]);
        $putResponse->assertStatus(403)
            ->assertJson(['error' => 'この操作を実行する権限がありません。']);

        $deleteResponse = $this->deleteJson("/api/v1/attendance-records/{$recordB->id}");
        $deleteResponse->assertStatus(403)
            ->assertJson(['error' => 'この操作を実行する権限がありません。']);
    }
}
