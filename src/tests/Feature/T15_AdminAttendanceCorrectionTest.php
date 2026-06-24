<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\AttendanceRecord;
use App\Models\StampCorrectionRequest;
use PHPUnit\Framework\Attributes\Test;

class T15_AdminAttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function 承認待ちの修正申請が全て表示されている(): void
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $attendance = AttendanceRecord::factory()->create(['user_id' => $user->id]);

        StampCorrectionRequest::create([
            'attendance_record_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'reason' => 'テスト理由',
            'requested_remarks' => '未承認の申請データ',
        ]);
        StampCorrectionRequest::create([
            'attendance_record_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'approved',
            'reason' => 'テスト理由',
            'requested_remarks' => '承認済みの申請データ',
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.request.list', ['tab' => 'pending']));

        $response->assertStatus(200);
        $response->assertSee('未承認の申請データ');
        $response->assertDontSee('承認済みの申請データ');
    }

    #[Test]
    public function 承認済みの修正申請が全て表示されている(): void
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $attendance = AttendanceRecord::factory()->create(['user_id' => $user->id]);

        StampCorrectionRequest::create([
            'attendance_record_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'reason' => 'テスト理由',
            'requested_remarks' => '未承認の申請データ',
        ]);
        StampCorrectionRequest::create([
            'attendance_record_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'approved',
            'reason' => 'テスト理由',
            'requested_remarks' => '承認済みの申請データ',
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.request.list', ['tab' => 'approved']));

        $response->assertStatus(200);
        $response->assertSee('承認済みの申請データ');
        $response->assertDontSee('未承認の申請データ');
    }

    #[Test]
    public function 修正申請の詳細内容が正しく表示されている(): void
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $attendance = AttendanceRecord::factory()->create(['user_id' => $user->id]);

        $requestData = StampCorrectionRequest::create([
            'attendance_record_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'reason' => 'テスト理由',
            'requested_remarks' => '詳細確認用テキストサンプル',
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.request.approve', ['id' => $requestData->id]));

        $response->assertStatus(200);
        $response->assertSee('詳細確認用テキストサンプル');
    }

    #[Test]
    public function 修正申請の承認処理が正しく行われる(): void
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        
        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '2026-06-24 09:00:00',
            'clock_out' => '2026-06-24 18:00:00',
        ]);

        $requestData = StampCorrectionRequest::create([
            'attendance_record_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'reason' => 'テスト理由',
            'requested_clock_in' => '08:30:00',
            'requested_clock_out' => '17:30:00',
            'requested_remarks' => '修正します',
        ]);

        $response = $this->actingAs($admin, 'admin')->patch(route('admin.request.approve.submit', ['id' => $requestData->id]), [
            'action' => 'approve'
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('stamp_correction_requests', [
            'id' => $requestData->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('attendance_records', [
            'id' => $attendance->id,
            'clock_in' => '08:30:00',
            'clock_out' => '17:30:00',
        ]);
    }
}
