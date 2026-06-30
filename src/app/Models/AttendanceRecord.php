<?php

namespace App\Models;

// データーベーステーブルとPHPを結びつけ操作する機能の読み込み
use Illuminate\Database\Eloquent\Model;
// 1対多のつながりで子から親データーを紐づけるリレーション機能の読み込み
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// 1対多のつながりで親から子データー一覧を紐づけるリレーション機能の読み込み
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// モデル機能を継承した勤怠登録データを扱うためのクラス
class AttendanceRecord extends Model
{
    use HasFactory;

    // 安全に一括保存(複数の代入)を許可するカラムの指定
    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'total_time',
        'total_break_time',
        'comment',
    ];

    // ユーザー情報に紐づけるための機能
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 休憩情報データ一覧に紐づけるための機能
    public function breaks(): HasMany
    {
        return $this->hasMany(BreakLog::class);
    }

    // 修正申請データーに紐づけるための機能
    public function applications(): HasMany
    {
        return $this->hasMany(StampCorrectionRequest::class);
    }
}
