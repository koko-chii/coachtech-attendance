<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceRecord;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;

class T18_ApiAttendanceWriteTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function 勤怠が作成される(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*'], 'sanctum');

        $postData = [
            'date' => '2026-06-30',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ];

        $response = $this->postJson('/api/v1/attendance-records', $postData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $user->id,
            'date' => '2026-06-30',
        ]);
    }

    #[Test]
    public function バリデーションエラー時に422と日本語エラーメッセージが返る(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*'], 'sanctum');

        $response = $this->postJson('/api/v1/attendance-records', []);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors']);
    }

    #[Test]
    public function 勤怠が更新される(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*'], 'sanctum');
        $record = AttendanceRecord::factory()->create(['user_id' => $user->id]);

        $updateData = [
            'date' => $record->date, // ★ 必須項目を追加
            'clock_in' => '10:00:00',
        ];

        $response = $this->putJson("/api/v1/attendance-records/{$record->id}", $updateData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('attendance_records', [
            'id' => $record->id,
            'clock_in' => '10:00:00',
        ]);

        $notFoundResponse = $this->putJson('/api/v1/attendance-records/99999', $updateData);
        $notFoundResponse->assertStatus(404);
    }

    #[Test]
    public function 勤怠が削除される(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*'], 'sanctum');
        $record = AttendanceRecord::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/v1/attendance-records/{$record->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('attendance_records', ['id' => $record->id]);

        $notFoundResponse = $this->deleteJson('/api/v1/attendance-records/99999');
        $notFoundResponse->assertStatus(404);
    }
}
