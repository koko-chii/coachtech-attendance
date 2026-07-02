<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AttendanceRecord;

// 勤怠データの操作ルールを定義するクラス
class AttendanceRecordPolicy
{
    // 全ての権限チェックの前の処理
    public function before(User $user, string $ability): ?bool
    {
        // 管理者の場合、以降の確認不要
        if ($user->admin_status === true) {
            return true;
        }

        // 管理者でない場合は通常ルール
        return null;
    }

    // 勤怠データを更新するユーザーの確認
    public function update(User $user, AttendanceRecord $attendanceRecord): bool
    {
        // スタッフIDを確認し自分の勤怠データだけ操作可能
        return $user->id === $attendanceRecord->user_id;
    }

    // 勤怠データを削除するユーザーの確認
    public function delete(User $user, AttendanceRecord $attendanceRecord): bool
    {   
        // スタッフIDを確認し、自分の勤怠データだけ操作可能
        return $user->id === $attendanceRecord->user_id;
    }
}
