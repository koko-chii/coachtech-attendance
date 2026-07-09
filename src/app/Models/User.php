<?php

namespace App\Models;

// ユーザーテストデーターを作成するための呼び出し
use Database\Factories\UserFactory;
// 大量のテストデーターを作成するための呼び出し
use Illuminate\Database\Eloquent\Factories\HasFactory;
// 勤怠管理画面に認証機能をするための呼出し
use Illuminate\Foundation\Auth\User as Authenticatable;
// ユーザー（従業員）にメールや画面で通知(ノティファイアブル)を送るための機能を呼び出し
use Illuminate\Notifications\Notifiable;
// メール認証機能の呼び出し
use Illuminate\Contracts\Auth\MustVerifyEmail;
// laravel標準の沢山のエロクワントリレーション機能(1対多)を使うための読み込み
use Illuminate\Database\Eloquent\Relations\HasMany;
// データーベースの勤怠登録データーを操作するAttendanceRecordモデルを使うための読み込み
use App\Models\AttendanceRecord;
// API用の認証システムトークン
use Laravel\Sanctum\HasApiTokens;

// 勤怠管理のユーザーにメール認証機能をするためのクラス(設置)
class User extends Authenticatable implements MustVerifyEmail
{
    // テストデータの大量生産と通知機能を組合わせる
    use HasApiTokens, HasFactory, Notifiable;

    // 名前とメールアドレス、パスワードはユーザーは書き換え可能
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'admin_status',
    ];

    // パスワードと自動ログインはガードされている(ヒドゥン)
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // データの形式を正しく変換するための関数(機能)
    protected function casts(): array
    {
        // メール認証の日時を日付形式に、パスワードは暗号化に変換して返す
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // 1対多のリレーションを使うための関数(機能)
    public function attendanceRecords(): HasMany
    {
        // ユーザーは複数存在する勤怠データ(1対多のリレーション)を引っ張ってくる
        return $this->hasMany(AttendanceRecord::class, 'user_id');
    }
}
