<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\AttendanceRecord;
use App\Models\BreakLog;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;

class T13_AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function 勤怠詳細画面に表示されるデータが選択したものになっている(): void
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create(['name' => '詳細テスト太郎']);
        $today = Carbon::today()->format('Y-m-d');

        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => $today,
            'clock_in' => $today . ' 09:00:00',
            'clock_out' => $today . ' 18:00:00',
            'comment' => 'テスト用備考サンプル',
        ]);

        BreakLog::create([
            'attendance_record_id' => $attendance->id,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.attendance.detail', ['id' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee('詳細テスト太郎');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee('テスト用備考サンプル');
    }

    #[Test]
    public function 出勤時間が退勤時間より後になっている場合エラーメッセージが表示される(): void
    {
        $admin = Admin::factory()->create();
        $attendance = AttendanceRecord::factory()->create();

        $response = $this->actingAs($admin, 'admin')->patch(route('admin.attendance.update', ['id' => $attendance->id]), [
            'clock_in' => '18:00',
            'clock_out' => '09:00',
            'comment' => '出勤時間エラーテスト',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です'
        ]);
    }

    #[Test]
    public function 休憩開始時間が退勤時間より後になっている場合エラーメッセージが表示される(): void
    {
        $admin = Admin::factory()->create();
        $attendance = AttendanceRecord::factory()->create();

        $response = $this->actingAs($admin, 'admin')->patch(route('admin.attendance.update', ['id' => $attendance->id]), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                [
                    'id' => null,
                    'break_in' => '19:00',
                    'break_out' => '20:00',
                ],
            ],
            'comment' => '休憩開始エラーテスト',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'breaks.0.break_in' => '休憩時間が不適切な値です'
        ]);
    }

    #[Test]
    public function 休憩終了時間が退勤時間より後になっている場合エラーメッセージが表示される(): void
    {
        $admin = Admin::factory()->create();
        $attendance = AttendanceRecord::factory()->create();

        $response = $this->actingAs($admin, 'admin')->patch(route('admin.attendance.update', ['id' => $attendance->id]), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                [
                    'id' => null,
                    'break_in' => '12:00',
                    'break_out' => '19:00',
                ],
            ],
            'comment' => '休憩終了エラーテスト',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'breaks.0.break_out' => '休憩時間もしくは退勤時間が不適切な値です'
        ]);
    }

    #[Test]
    public function 備考欄が未入力の場合のエラーメッセージが表示される(): void
    {
        $admin = Admin::factory()->create();
        $attendance = AttendanceRecord::factory()->create();

        $response = $this->actingAs($admin, 'admin')->patch(route('admin.attendance.update', $attendance), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'comment' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'comment'
        ]);
    }
}
