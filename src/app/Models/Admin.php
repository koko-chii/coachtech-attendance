<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// 管理者情報を管理するモデル
class Admin extends Model
{
    // テスト用ダミーデータを作成
    use HasFactory;

    // 名前・メールアドレス・パスワードを一括代入を許可
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
}