<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;
use App\Models\AttendanceRecord;

// 管理者の勤怠管理に関する処理を行うクラス
class AdminAttendanceController extends Controller
{
    // 管理者用勤怠一覧画面を表示
    public function showDailyList(Request $request): View
    {
        // リクエストから日付を取得し、なければ当日の日付を設定
        $dateStr = $request->query('date', Carbon::now()->format('Y-m-d'));
        $date = Carbon::parse($dateStr);

        // 指定された日付の勤怠データを、一般ユーザー情報および休憩データと一緒に取得
        // ユーザーが登録されていて打刻がない場合も考慮し、データが存在するもののみ取得
        $attendanceRecords = AttendanceRecord::with(['user', 'breakLogs'])
            ->where('date', $dateStr)
            ->get();

        // 勤怠一覧画面へ日付データと勤怠データを渡して表示
        return view('admin.admin_attendance_list', compact('date', 'attendanceRecords'));
    }

    // 管理者用勤怠詳細画面を表示
    public function showDetail(int $id): View
    {
        // 指定されたユーザーの勤怠データと休憩データを一緒に取得
        $attendance = AttendanceRecord::with(['user', 'breakLogs'])->findOrFail($id);

        // 管理者用勤怠詳細画面を返す
        return view('admin.admin_attendance_detail', compact('attendance'));
    }
}
