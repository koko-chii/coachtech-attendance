<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            // 💡 一般ユーザー（usersテーブル）と紐付けるための外部キーです
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // 💡 勤務日、出勤時間、退勤時間を保存します（退勤は最初は空なのでnullableにします）
            $table->date('date');
            $table->time('clock_in');
            $table->time('clock_out')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
