<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceRecord;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class T9_AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_自分が行った勤怠情報が全て表示されている(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-09'));

        $record = AttendanceRecord::factory()->create([
            'user_id' => $this->user->id,
            'date' => '2026-06-09',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($this->user)->get(route('attendance.list'));

        $response->assertStatus(200);
        $response->assertSee('06/09');
        $response->assertSee('詳細');
    }

    public function test_勤怠一覧画面に遷移した際に現在の月が表示される(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15'));

        $response = $this->actingAs($this->user)->get(route('attendance.list'));

        $response->assertStatus(200);
        $response->assertSee('2026年06月');
    }

    public function test_前月を押下した時に表示月の前月の情報が表示される(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15'));

        $response = $this->actingAs($this->user)->get(route('attendance.list', ['month' => '2026-05']));

        $response->assertStatus(200);
        $response->assertSee('2026年05月');
    }

    public function test_翌月を押下した時に表示月の翌月の情報が表示される(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15'));

        $response = $this->actingAs($this->user)->get(route('attendance.list', ['month' => '2026-07']));

        $response->assertStatus(200);
        $response->assertSee('2026年07月');
    }

    public function test_詳細を押下するとその日の勤怠詳細画面に遷移する(): void
    {
        $record = AttendanceRecord::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($this->user)->get(route('attendance.list'));

        $response->assertStatus(200);
        $response->assertSee('詳細');
    }
}
