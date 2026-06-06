<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceRecord;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;

class T6_ClockInTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function 出勤ボタンが正しく機能する()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤');

        $this->actingAs($user)->post('/attendance/clock-in');
        
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤中');
    }

    #[Test]
    public function 出勤は一日一回のみできる()
    {
        $user = User::factory()->create();
        AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'clock_in' => Carbon::now()->subHours(8)->toTimeString(),
            'clock_out' => Carbon::now()->toTimeString(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertDontSee('出勤');
    }

    #[Test]
    public function 出勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();
        Carbon::setTestNow(Carbon::create(2026, 6, 6, 9, 0, 0));

        $this->actingAs($user)->post('/attendance/clock-in');

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertSee('09:00');

        Carbon::setTestNow();
    }
}
