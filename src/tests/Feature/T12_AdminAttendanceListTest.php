<?php

namespace Tests\Feature\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;
use App\Models\AttendanceRecord;
use PHPUnit\Framework\Attributes\Test;

class T12_AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function 遷移した際に現在の日付が表示される(): void
    {
        $admin = User::factory()->create(['admin_status' => true]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.list'));

        $response->assertStatus(200);

        $todayString = Carbon::today()->isoFormat('YYYY年M月D日');
        $response->assertSee($todayString);
    }

    #[Test]
    public function 前日を押下した時に前の日の勤怠情報が表示される(): void
    {
        $admin = User::factory()->create(['admin_status' => true]);

        $previousDay = Carbon::today()->subDay()->format('Y-m-d');
        $previousDayDisplay = Carbon::today()->subDay()->isoFormat('YYYY年M月D日');

        $response = $this->actingAs($admin)->get(route('admin.attendance.list', ['date' => $previousDay]));

        $response->assertStatus(200);
        $response->assertSee($previousDayDisplay);
    }

    #[Test]
    public function 翌日を押下した時に次の日の勤怠情報が表示される(): void
    {
        $admin = User::factory()->create(['admin_status' => true]);

        $nextDay = Carbon::today()->addDay()->format('Y-m-d');
        $nextDayDisplay = Carbon::today()->addDay()->isoFormat('YYYY年M月D日');

        $response = $this->actingAs($admin)->get(route('admin.attendance.list', ['date' => $nextDay]));

        $response->assertStatus(200);
        $response->assertSee($nextDayDisplay);
    }

    #[Test]
    public function その日になされた全ユーザーの勤怠情報が正確に確認できる(): void
    {
        $admin = User::factory()->create(['admin_status' => true]);

        $user1 = User::factory()->create(['name' => 'テスト太郎']);
        $user2 = User::factory()->create(['name' => 'テスト次郎']);

        $today = Carbon::today()->format('Y-m-d');

        AttendanceRecord::factory()->create([
            'user_id' => $user1->id,
            'date' => $today,
            'clock_in' => $today . ' 09:00:00',
            'clock_out' => $today . ' 18:00:00',
        ]);

        AttendanceRecord::factory()->create([
            'user_id' => $user2->id,
            'date' => $today,
            'clock_in' => $today . ' 10:00:00',
            'clock_out' => $today . ' 19:00:00',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.list'));

        $response->assertStatus(200);
        $response->assertSee('テスト太郎');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('テスト次郎');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }
}
