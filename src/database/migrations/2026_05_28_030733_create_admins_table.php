<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //管理者ユーザーテーブルを作成するための設計指示
        //テーブルにしまうカラムの定義
        //管理者テーブルID、管理者の名前、管理者のメールアドレス、パスワード、管理者テーブル作成日時
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });
    }

   //マイグレーションを削除するための関数(機能)
    public function down(): void
    {
        //管理者テーブルもまとめて削除する設計指示
        Schema::dropIfExists('admins');
    }
};
