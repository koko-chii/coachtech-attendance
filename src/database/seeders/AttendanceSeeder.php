<?php

namespace Database\Seeders;

//laravel標準機能のSeeder機能(初期データー投入機能)の読み込み
use Illuminate\Database\Seeder;
//ユーザー情報のデーターベース操作機能(Userモデル)を使うための読み込み
use App\Models\User;
//管理者情報のデーターベース操作機能(Adminモデル)を使うための読み込み
use App\Models\Admin;
//データーベースの休憩データーを操作するBreakLogモデルを使うための読み込み
use App\Models\BreakLog;
//勤怠管理のモデルを使うための読み込み
use App\Models\AttendanceRecord;
use Illuminate\Support\Facades\Hash;
//日時を取得・計算するための機能の読み込み
use Carbon\Carbon;

//Seeder機能を継承したオリジナルの勤怠管理初期データー投入機能を作成するためのクラス(設置)
class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        // ユーザー情報1 の名前、メールアドレス、パスワードの暗号化、メール認証日時は現在時刻
        $user1 = User::create([
            'name' => 'ユーザー1',
            'email' => 'user1@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        //ユーザー情報2
        User::create([
            'name' => 'ユーザー2',
            'email' => 'user2@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // ユーザー情報3(管理者テーブルへ保存) メール認証不要
        User::create([
            'name' => 'ユーザー3(管理者)',
            'email' => 'user3@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'admin_status' => true,
        ]);

        // 今現在の日時を取得してnow変数(箱)へしまう
        $now = Carbon::now();

        // 過去5ヶ月分のデータを順番に作成(5か月前、4か月前....1か月前)
        //現在から5か月前に戻り、月初めの1日から作成する
        //出勤日数のカウント開始のためにリセットする
        for ($i = 5; $i >= 1; $i--) {
            $monthDate = (clone $now)->subMonths($i)->startOfMonth();
            $createdDays = 0;

            //ユーザー1へのデーター投入
            // 平日15日分データーができるまで打刻を繰り返す
            // 9時から18時の勤務のデーターを登録する
            //平日9時18時の勤務で1回分増やしてcreatedDays変数(箱)にしまう
            //ズレるのを防ぐため毎日、日付を1日進めて$monthDateを上書きする
            while ($createdDays < 15) {
                if (!$monthDate->isWeekend()) {
                    $this->createRecord($user1->id, $monthDate, '09:00:00', '18:00:00');
                    $createdDays++;
                }
                $monthDate->addDay();
            }
        }

        //当月のデータ作成 (特殊パターン)
        $currentMonthDate = (clone $now)->startOfMonth();
        $patterns = [
            ...array_fill(0, 10, ['09:00:00', '18:00:00']), // 通常 10日
            ...array_fill(0, 3,  ['09:00:00', '20:00:00']), // 残業 3日 (9:00-20:00)
            ...array_fill(0, 2,  ['09:30:00', '18:00:00']), // 遅刻 2日 (9:30-18:00)
            ...array_fill(0, 1,  ['09:00:00', '17:00:00']), // 早退 1日 (9:00-17:00)
            ...array_fill(0, 1,  ['08:00:00', '21:00:00']), // 長時間労働 1日 (8:00-21:00)
        ];

        //ユーザー1へのデーター投入
        //特殊パターンの出勤17日分を繰り返す
        //土日の場合スキップする
        //土日は1日進める
        //平日になったら出退勤打刻を登録する
        //カレンダーを1日進めておく
        foreach ($patterns as $pattern) {
            while ($currentMonthDate->isWeekend()) {
                $currentMonthDate->addDay();
            }
            $this->createRecord($user1->id, $currentMonthDate, $pattern[0], $pattern[1]);
            $currentMonthDate->addDay();
        }
    }

    //ユーザーID、日付、出退勤時刻を個別に作成するための関数(設置)
    private function createRecord(int $userId, Carbon $date, string $startTime, string $endTime): void
    {
        //年月日をデーター形式でdateStr変数(箱)にしまう
        $dateStr = $date->format('Y-m-d');

        //勤怠管理テーブルに打刻データーを登録し、そのIDを取得する
        //従業員ID、勤務日時形式、出退勤時刻(出勤時の退勤は空っぽでOK)、新規作成・更新を保存する
        $attendance = AttendanceRecord::create([
            'user_id' => $userId,
            'date' => $dateStr,
            'clock_in' => $startTime,
            'clock_out' => $endTime,
        ]);

        //休憩テーブルに打刻データを登録する
        //休憩入戻時刻、新規作成・更新データーを保存する
        BreakLog::create([
            'attendance_record_id' => $attendance->id,
            'break_in' => '12:00:00',
            'break_out' => '13:00:00',
        ]);
    }
}
