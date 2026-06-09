<?php

namespace Database\Factories;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

//テスト機能Factoryを継承した独自のAttendanceRecordFactoryを
//作成するためのクラス(設置)
class AttendanceRecordFactory extends Factory
{
    //勤怠登録データーを操作する勤怠登録モデルを用意
    protected $model = AttendanceRecord::class;

    //初期設定を設定する関数(機能)
    //(ユーザー情報、テスト用今日の日付、出勤時刻9時、退勤時刻18時)
    public function definition(): array
    {
        return [
            'user_id'   => User::factory(),
            'date'      => $this->faker->date(),
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
        ];
    }
}
