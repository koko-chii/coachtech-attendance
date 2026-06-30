<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceRecord;
use PHPUnit\Framework\Attributes\Test;

class T17_ApiAttendanceReadTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function 勤怠一覧がJSONで取得できる(): void
    {
        $user = User::factory()->create();
        AttendanceRecord::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/v1/attendance-records');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'last_page', 'per_page', 'total']
            ]);
    }

    #[Test]
    public function 勤怠詳細がJSONで取得できる(): void
    {
        $user = User::factory()->create();
        $record = AttendanceRecord::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson("/api/v1/attendance-records/{$record->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                'id',
                'user_id',
                'date',
                'clock_in',
                'clock_out',
                'user',
                'breaks',
                'stamp_correction_requests'
                ]
            ]);
    }

    #[Test]
    public function 存在しないIDでは404とエラーJSONが返る(): void
    {
        $response = $this->getJson('/api/v1/attendance-records/99999');

        $response->assertStatus(404)
            ->assertJson(['error' => '勤怠情報が見つかりませんでした。']);
    }
}
