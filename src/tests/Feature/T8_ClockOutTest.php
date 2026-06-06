<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceRecord;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;

class T8_ClockOutTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function 退勤ボタンが正しく機能する()
    {
        $user = User::factory()->create();
        AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'clock_in' => Carbon::now()->toTimeString(),
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤');

        $this->actingAs($user)->post('/attendance/clock-out');

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤済');
    }

    #[Test]
    public function 退勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();
        Carbon::setTestNow(Carbon::create(2026, 6, 6, 9, 0, 0));
        $this->actingAs($user)->post('/attendance/clock-in');

        Carbon::setTestNow(Carbon::create(2026, 6, 6, 18, 0, 0));
        $this->actingAs($user)->post('/attendance/clock-out');

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertSee('18:00');

        Carbon::setTestNow();
    }
}
