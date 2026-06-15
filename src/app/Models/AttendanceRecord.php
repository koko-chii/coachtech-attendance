<?php

namespace App\Models;

//データーベースの保存を許可すためのエロクワントモデルを呼出し
use Illuminate\Database\Eloquent\Model;
//laravel標準の大量のテスト機能を作成する機能を読み込み
use Illuminate\Database\Eloquent\Factories\HasFactory;
//laravel標準の沢山のエロクワントリレーション機能(1対多)を使うための読み込み
use Illuminate\Database\Eloquent\Relations\HasMany;
//データーベースの休憩情報を操作するBreakLogモデルを使うための読み込み
use App\Models\BreakLog;
//修正申請データーを操作する修正申請モデルの読み込み
use App\Models\StampCorrectionRequest;
//1対多のリレーション機能を使うための読み込み
use Illuminate\Database\Eloquent\Relations\HasOne;
//Laravel標準の勤怠データの持ち主ユーザーを取得するための機能を読み込む
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

//勤怠管理モデルを作成するためのクラス(設置)
class AttendanceRecord extends Model
{
    use HasFactory;

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
    public function breakLogs(): HasMany
    {
        //勤怠管理は複数存在する休憩データ(1対多のリレーション)を引っ張ってきて
        // コントローラーに返し画面に表示する
        return $this->hasMany(BreakLog::class, 'attendance_record_id');
    }

    //勤怠データー(親)と修正申請データー(子)を紐づける1対多のリレーション
    public function stampCorrectionRequest(): HasOne
    {
        return $this->hasOne(StampCorrectionRequest::class, 'attendance_record_id');
    }

    public function user(): BelongsTo
    {
        // 勤怠データは1人のユーザーに属する
        return $this->belongsTo(User::class);
    }
}
