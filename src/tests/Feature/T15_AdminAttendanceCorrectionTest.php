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
        $attendance1 = AttendanceRecord::factory()->create(['user_id' => $user->id]);
        $attendance2 = AttendanceRecord::factory()->create(['user_id' => $user->id]);

        StampCorrectionRequest::create([
            'attendance_record_id' => $attendance1->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'reason' => 'テスト理由',
            'requested_comment' => '承認待ちの申請データ',
        ]);
        StampCorrectionRequest::create([
            'attendance_record_id' => $attendance2->id,
            'user_id' => $user->id,
            'status' => 'approved',
            'reason' => 'テスト理由',
            'requested_comment' => '承認済みの申請データ',
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.request.list', ['tab' => 'pending']));

        $response->assertStatus(200);
        $response->assertSee('承認待ちの申請データ');
        $response->assertDontSee('承認済みの申請データ');
    }

    #[Test]
    public function 承認済みの修正申請が全て表示されている(): void
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $attendance1 = AttendanceRecord::factory()->create(['user_id' => $user->id]);
        $attendance2 = AttendanceRecord::factory()->create(['user_id' => $user->id]);

        StampCorrectionRequest::create([
            'attendance_record_id' => $attendance1->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'reason' => 'テスト理由',
            'requested_comment' => '承認待ちの申請データ',
        ]);
        StampCorrectionRequest::create([
            'attendance_record_id' => $attendance2->id,
            'user_id' => $user->id,
            'status' => 'approved',
            'reason' => 'テスト理由',
            'requested_comment' => '承認済みの申請データ',
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.request.list', ['tab' => 'approved']));

        $response->assertStatus(200);
        $response->assertSee('承認済みの申請データ');
        $response->assertDontSee('承認待ちの申請データ');
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
            'requested_comment' => '詳細確認用コメント',
        ]);

        $response = $this->actingAs($admin, 'admin')->get(route('admin.request.approve', ['attendance_correct_request_id' => $requestData->id]));

        $response->assertStatus(200);
        $response->assertSee('詳細確認用コメント');
        $response->assertSee('テスト理由');
    }

    #[Test]
    public function 修正申請の承認処理が正しく行われる(): void
    {
        $admin = Admin::factory()->create();
        $user = User::factory()->create();
        $attendance = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00'
        ]);

        $requestData = StampCorrectionRequest::create([
            'attendance_record_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in' => '10:00:00',
            'requested_clock_out' => '19:00:00',
            'requested_comment' => '承認後に反映される備考',
            'status' => 'pending',
            'reason' => 'テスト理由',
        ]);

        $response = $this->actingAs($admin, 'admin')->post(route('admin.request.approve', ['attendance_correct_request_id' => $requestData->id]), [
            'action' => 'approve'
        ]);

        $response->assertStatus(302);
        
        $this->assertDatabaseHas('attendance_records', [
            'id' => $attendance->id,
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'comment' => '承認後に反映される備考',
        ]);

        $this->assertDatabaseHas('stamp_correction_requests', [
            'id' => $requestData->id,
            'status' => 'approved',
        ]);
    }
}
