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
        // 💡 シーダーの記述に合わせてテーブル名を「breaks」として作成します
        Schema::create('breaks', function (Blueprint $table) {
            $table->id();
            // 💡 どの出勤に対する休憩なのかを紐付ける外部キーです
            $table->foreignId('attendance_record_id')->constrained()->cascadeOnDelete();
            // 💡 休憩開始時間、休憩終了時間を保存します（休憩終了はnullableにします）
            $table->time('break_in');
            $table->time('break_out')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('breaks');
    }
};
