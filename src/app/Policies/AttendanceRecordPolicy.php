<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AttendanceRecord;

class AttendanceRecordPolicy
{
    public function update(User $user, AttendanceRecord $attendanceRecord): bool
    {
        return $user->id === $attendanceRecord->user_id || $user->admin_status === true;
    }

    public function delete(User $user, AttendanceRecord $attendanceRecord): bool
    {
        return $user->id === $attendanceRecord->user_id || $user->admin_status === true;
    }
}
