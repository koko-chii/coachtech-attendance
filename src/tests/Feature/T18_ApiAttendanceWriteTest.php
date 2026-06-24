<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceRecord;
use PHPUnit\Framework\Attributes\Test;

class T18_ApiAttendanceWriteTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function 正常なデータ送信で勤怠が作成される(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $data = [
            'user_id' => $user->id,
            'date' => '2026-06-24',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ];

        $response = $this->postJson('/api/v1/attendance-records', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('attendance_records', ['date' => '2026-06-24']);
    }

    #[Test]
    public function バリデーションエラー時に422と日本語エラーメッセージが返る(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/v1/attendance-records', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date', 'clock_in']);
    }

    #[Test]
    public function 既存勤怠に対してPUTでレコードが更新される(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $record = AttendanceRecord::factory()->create(['user_id' => $user->id, 'date' => '2026-06-24']);

        $data = [
            'date' => '2026-06-24',
            'clock_in' => '09:30:00',
        ];

        $response = $this->putJson("/api/v1/attendance-records/{$record->id}", $data);

        $response->assertStatus(200);
        $this->assertDatabaseHas('attendance_records', ['id' => $record->id, 'clock_in' => '09:30:00']);
    }

    #[Test]
    public function 存在しないIDに対してPUTを実行すると404を返す(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $response = $this->putJson('/api/v1/attendance-records/99999', [
            'date' => '2026-06-24',
            'clock_in' => '09:00:00'
        ]);

        $response->assertStatus(404);
    }

    #[Test]
    public function 既存勤怠に対してDELETEを送信するとレコードが削除される(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');
        $record = AttendanceRecord::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/v1/attendance-records/{$record->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('attendance_records', ['id' => $record->id]);
    }

    #[Test]
    public function 存在しないIDに対してDELETEを実行すると404を返す(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $response = $this->deleteJson('/api/v1/attendance-records/99999');

        $response->assertStatus(404);
    }
}
