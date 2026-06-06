<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceRecord;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;

class T7_BreakTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function 休憩ボタンが正しく機能する()
    {
        $user = User::factory()->create();
        AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'clock_in' => Carbon::now()->toTimeString(),
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩入');

        $this->actingAs($user)->post('/attendance/break');

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩中');
    }

    #[Test]
    public function 休憩は一日に何回でもできる()
    {
        $user = User::factory()->create();
        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'clock_in' => Carbon::now()->toTimeString(),
            'clock_out' => null,
        ]);

        DB::table('breaks')->insert([
            'attendance_record_id' => $attendance->id,
            'break_in' => Carbon::now()->subMinutes(30)->toTimeString(),
            'break_out' => Carbon::now()->subMinutes(15)->toTimeString(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        
        $response->assertSee('休憩入');
    }

    #[Test]
    public function 休憩戻ボタンが正しく機能する()
    {
        $user = User::factory()->create();
        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'clock_in' => Carbon::now()->toTimeString(),
            'clock_out' => null,
        ]);

        DB::table('breaks')->insert([
            'attendance_record_id' => $attendance->id,
            'break_in' => Carbon::now()->toTimeString(),
            'break_out' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩戻');

        $this->actingAs($user)->post('/attendance/break');

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤中');
    }

    #[Test]
    public function 休憩戻は一日に何回でもできる()
    {
        $user = User::factory()->create();
        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'clock_in' => Carbon::now()->toTimeString(),
            'clock_out' => null,
        ]);

        DB::table('breaks')->insert([
            'attendance_record_id' => $attendance->id,
            'break_in' => Carbon::now()->subMinutes(30)->toTimeString(),
            'break_out' => Carbon::now()->subMinutes(15)->toTimeString(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('breaks')->insert([
            'attendance_record_id' => $attendance->id,
            'break_in' => Carbon::now()->toTimeString(),
            'break_out' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        
        $response->assertSee('休憩戻');
    }

    #[Test]
    public function 休憩時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();
        Carbon::setTestNow(Carbon::create(2026, 6, 6, 9, 0, 0));
        $this->actingAs($user)->post('/attendance/clock-in');

        Carbon::setTestNow(Carbon::create(2026, 6, 6, 12, 0, 0));
        $this->actingAs($user)->post('/attendance/break');

        Carbon::setTestNow(Carbon::create(2026, 6, 6, 13, 0, 0));
        $this->actingAs($user)->post('/attendance/break');

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertSee('12:00');
        $response->assertSee('13:00');

        Carbon::setTestNow();
    }
}
