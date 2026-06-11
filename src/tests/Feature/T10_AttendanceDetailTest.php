<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\BreakLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * T10: 勤怠詳細情報取得機能のテスト
 */
class T10_AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function 勤怠詳細画面の名前がログインユーザーの氏名になっている(): void
    {
        $user = User::factory()->create([
            'name' => 'テスト太郎',
        ]);

        $record = AttendanceRecord::factory()->create([
            'user_id'   => $user->id,
            'date'      => '2026-06-11',
            'clock_in'  => '2026-06-11 09:00:00',
            'clock_out' => '2026-06-11 18:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.detail', $record->id));

        $response->assertStatus(200);
        $response->assertSee('テスト太郎');
    }

    #[Test]
    public function 勤怠詳細画面の日付が選択した日付になっている(): void
    {
        $user = User::factory()->create();

        $record = AttendanceRecord::factory()->create([
            'user_id'   => $user->id,
            'date'      => '2026-06-11',
            'clock_in'  => '2026-06-11 09:00:00',
            'clock_out' => '2026-06-11 18:00:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.detail', $record->id));

        $response->assertStatus(200);
        $response->assertSee('2026年');
        $response->assertSee('6月11日');
    }

    #[Test]
    public function 出勤退勤にて記されている時間がログインユーザーの打刻と一致している(): void
    {
        $user = User::factory()->create();

        $record = AttendanceRecord::factory()->create([
            'user_id'   => $user->id,
            'date'      => '2026-06-11',
            'clock_in'  => '2026-06-11 09:15:00', // テスト用に特徴的な時間にする
            'clock_out' => '2026-06-11 18:45:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.detail', $record->id));

        $response->assertStatus(200);
        $response->assertSee('09:15');
        $response->assertSee('18:45');
    }

    #[Test]
    public function 休憩にて記されている時間がログインユーザーの打刻と一致している(): void
    {
        $user = User::factory()->create();

        $record = AttendanceRecord::factory()->create([
            'user_id'   => $user->id,
            'date'      => '2026-06-11',
            'clock_in'  => '2026-06-11 09:00:00',
            'clock_out' => '2026-06-11 18:00:00',
        ]);

        // factoryエラーを回避するため直接create保存
        BreakLog::create([
            'attendance_record_id' => $record->id,
            'break_in'             => '2026-06-11 12:15:00', // テスト用に特徴的な時間にする
            'break_out'            => '2026-06-11 13:45:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.detail', $record->id));

        $response->assertStatus(200);
        $response->assertSee('12:15');
        $response->assertSee('13:45');
    }
}
