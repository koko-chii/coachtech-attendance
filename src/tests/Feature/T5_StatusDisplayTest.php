<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceRecord;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;

class T5_StatusDisplayTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function 勤務外の場合_勤怠ステータスが正しく表示される()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('勤務外');
    }

    #[Test]
    public function 出勤中の場合_勤怠ステータスが正しく表示される()
    {
        $user = User::factory()->create();
        AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'clock_in' => Carbon::now()->toTimeString(),
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('出勤中');
    }

    #[Test]
    public function 休憩中の場合_勤怠ステータスが正しく表示される()
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

        $response->assertSee('休憩中');
    }

    #[Test]
    public function 退勤済の場合_勤怠ステータスが正しく表示される()
    {
        $user = User::factory()->create();
        AttendanceRecord::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'clock_in' => Carbon::now()->subHours(8)->toTimeString(),
            'clock_out' => Carbon::now()->toTimeString(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('退勤済');
    }
}
