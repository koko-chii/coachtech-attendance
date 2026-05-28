<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    // ⭕ データベースの保存を許可する項目をここに書くのが正解です
    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
    ];

    public function breaks()
    {
        return $this->hasMany(BreakLog::class, 'attendance_record_id'); 
    }
}
