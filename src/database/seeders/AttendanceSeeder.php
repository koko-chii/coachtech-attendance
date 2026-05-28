<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        // 1. ユーザー情報・管理者情報の作成 (要件通り)
        $user1 = User::create([
            'name' => 'ユーザー1',
            'email' => 'user1@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'ユーザー2',
            'email' => 'user2@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // ※管理者（admin_status=trueの代わりにadminsテーブルへ登録）
        Admin::create([
            'name' => 'ユーザー3(管理者)',
            'email' => 'user3@example.com',
            'password' => Hash::make('password'),
        ]);

        // 2. ★ user1 の意図的データの作成
        $now = Carbon::now();

        // --- 過去5ヶ月分のデータ作成 (平日15日 × 5ヶ月 = 75日 通常勤務) ---
        for ($i = 5; $i >= 1; $i--) {
            $monthDate = (clone $now)->subMonths($i)->startOfMonth();
            $createdDays = 0;

            while ($createdDays < 15) {
                if (!$monthDate->isWeekend()) {
                    $this->createRecord($user1->id, $monthDate, '09:00:00', '18:00:00');
                    $createdDays++;
                }
                $monthDate->addDay();
            }
        }

        // --- 当月のデータ作成 (計17日分の特殊パターン) ---
        $currentMonthDate = (clone $now)->startOfMonth();
        $patterns = [
            ...array_fill(0, 10, ['09:00:00', '18:00:00']), // 通常 10日
            ...array_fill(0, 3,  ['09:00:00', '20:00:00']), // 残業 3日 (9:00-20:00)
            ...array_fill(0, 2,  ['09:30:00', '18:00:00']), // 遅刻 2日 (9:30-18:00)
            ...array_fill(0, 1,  ['09:00:00', '17:00:00']), // 早退 1日 (9:00-17:00)
            ...array_fill(0, 1,  ['08:00:00', '21:00:00']), // 長時間労働 1日 (8:00-21:00)
        ];

        foreach ($patterns as $pattern) {
            // 平日のみに配置する
            while ($currentMonthDate->isWeekend()) {
                $currentMonthDate->addDay();
            }
            $this->createRecord($user1->id, $currentMonthDate, $pattern[0], $pattern[1]);
            $currentMonthDate->addDay();
        }
    }

    // レコード作成用のお助けメソッド (固定休憩も自動付与)
    private function createRecord($userId, $date, $startTime, $endTime)
    {
        $dateStr = $date->format('Y-m-d');

        // まだテーブルが作成されていない場合はエラーを防ぐため仮のテーブル名にしています
        // 必要に応じてマイグレーション完了後に実際のテーブル名に合わせてください
        $attendanceId = DB::table('attendance_records')->insertGetId([
            'user_id' => $userId,
            'date' => $dateStr,
            'clock_in' => $startTime,
            'clock_out' => $endTime,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('breaks')->insert([
            'attendance_record_id' => $attendanceId,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
