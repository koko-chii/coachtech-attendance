<?php

namespace App\Models;

//データーベースの保存を許可すためのエロクワントモデルを呼出し
use Illuminate\Database\Eloquent\Model;

//勤怠管理モデルを作成するためのクラス(設置)
class AttendanceRecord extends Model
{
    // データーベースに保存するための項目(ユーザー情報、月日、出勤・退勤時刻)を指定
    //フィラブルプロパティとは、データベースにデータを保存する際に、
    // 「画面から勝手に書き換えられても良い項目」を指定すること
    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
    ];

    //複数の休憩情報を取得するための関数(機能)
    public function breaks()
    {
        //勤怠管理は複数存在する休憩データ(1対多のリレーション)を引っ張ってきて
        // コントローラーに返し画面に表示する
        return $this->hasMany(BreakLog::class, 'attendance_record_id');
    }
}
