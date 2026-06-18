<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BreakLog extends Model
{
    // 実際のテーブル名を指定
    protected $table = 'breaks';

    // 1回分の休憩データ一式を一括保存する項目の指定
    protected $fillable = [
        'attendance_record_id',
        'break_in',
        'break_out',
    ];
}
