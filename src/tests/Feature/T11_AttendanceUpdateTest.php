<?php

namespace Tests\Feature;

use App\Models\AttendanceRecord;
use App\Models\BreakLog;
use App\Models\StampCorrectionRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * T11: 勤怠詳細情報修正機能のテスト
 */
class T11_AttendanceUpdateTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function 出勤時間が退勤時間より後になっている場合エラーメッセージが表示される(): void
    {
        $user = User::factory()->create();
        $record = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-06-11',
            'clock_in' => '09:00',
            'clock_out' => '18:00'
        ]);

        $response = $this->actingAs($user)->patch(route('attendance.update', $record->id), [
            'clock_in' => '19:00',
            'clock_out' => '18:00',
            'remarks' => 'テスト備考',
        ]);

        $response->assertSessionHasErrors(['clock_in' => '出勤時間もしくは退勤時間が不適切な値です']);
    }

    #[Test]
    public function 休憩開始時間が退勤時間より後になっている場合エラーメッセージが表示される(): void
    {
        $user = User::factory()->create();
        $record = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-06-11',
            'clock_in' => '09:00',
            'clock_out' => '18:00'
        ]);
        
        $break = BreakLog::create([
            'attendance_record_id' => $record->id,
            'break_in' => '12:00',
            'break_out' => '13:00'
        ]);

        $response = $this->actingAs($user)->patch(route('attendance.update', $record->id), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                $break->id => [
                    'break_in' => '19:00',
                    'break_out' => '19:30',
                ],
            ],
            'remarks' => 'テスト備考',
        ]);

        $response->assertSessionHasErrors(['breaks.' . $break->id . '.break_in' => '休憩時間が不適切な値です']);
    }

    #[Test]
    public function 休憩終了時間が退勤時間より後になっている場合エラーメッセージが表示される(): void
    {
        $user = User::factory()->create();
        $record = AttendanceRecord::factory()->create([
            'user_id' => $user->id,
            'date' => '2026-06-11',
            'clock_in' => '09:00',
            'clock_out' => '18:00'
        ]);
        
        $break = BreakLog::create([
            'attendance_record_id' => $record->id,
            'break_in' => '12:00',
            'break_out' => '13:00'
        ]);

        $response = $this->actingAs($user)->patch(route('attendance.update', $record->id), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                $break->id => [
                    'break_in' => '12:00',
                    'break_out' => '19:00',
                ],
            ],
            'remarks' => 'テスト備考',
        ]);

        $response->assertSessionHasErrors(['breaks.' . $break->id . '.break_out' => '休憩時間もしくは退勤時間が不適切な値です']);
    }

    #[Test]
    public function 備考欄が未入力の場合のエラーメッセージが表示される(): void
    {
        $user = User::factory()->create();
        $record = AttendanceRecord::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->patch(route('attendance.update', $record->id), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'remarks' => '',
        ]);

        $response->assertSessionHasErrors(['remarks' => '備考を記入してください']);
    }

    #[Test]
    public function 修正申請処理が実行される(): void
    {
        $user = User::factory()->create();
        $record = AttendanceRecord::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->patch(route('attendance.update', $record->id), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'remarks' => '修正申請テスト理由',
        ]);

        $this->assertDatabaseHas('stamp_correction_requests', [
            'user_id' => $user->id,
            'attendance_record_id' => $record->id,
            'status' => 'pending',
            'reason' => '修正申請テスト理由',
        ]);
    }

    #[Test]
    public function 承認待ちにログインユーザーが行った申請が全て表示されていること(): void
    {
        $user = User::factory()->create();
        $record = AttendanceRecord::factory()->create(['user_id' => $user->id]);
        
        StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_record_id' => $record->id,
            'status' => 'pending',
            'reason' => '承認待ちテスト理由表示確認',
        ]);

        $response = $this->actingAs($user)->get(route('attendance_correction_request.index'));
        $response->assertStatus(200);
        $response->assertSee('承認待ちテスト理由表示確認');
    }

    #[Test]
    public function 承認済みに管理者が承認した修正申請が全て表示されている(): void
    {
        $user = User::factory()->create();
        $record = AttendanceRecord::factory()->create(['user_id' => $user->id]);
        
        StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_record_id' => $record->id,
            'status' => 'approved',
            'reason' => '承認済みテスト理由表示確認',
        ]);

        $response = $this->actingAs($user)->get(route('attendance_correction_request.index'));
        $response->assertStatus(200);
        $response->assertSee('承認済みテスト理由表示確認');
    }

    #[Test]
    public function 各申請の詳細を押下すると勤怠詳細画面に遷移する(): void
    {
        $user = User::factory()->create();
        $record = AttendanceRecord::factory()->create(['user_id' => $user->id]);
        
        $request = StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_record_id' => $record->id,
            'status' => 'pending',
            'reason' => '詳細遷移確認用',
        ]);

        $response = $this->actingAs($user)->get(route('attendance_correction_request.index'));
        $response->assertStatus(200);
        $response->assertSee(route('attendance.detail', $request->attendance_record_id));
    }
}
