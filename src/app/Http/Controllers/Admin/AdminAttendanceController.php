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
        // リクエストから日付を取得し、未指定なら当日の日付を設定
        $dateStr = $request->query('date', Carbon::now()->format('Y-m-d'));
        $date = Carbon::parse($dateStr);

        // 当日の勤怠データを、従業員ユーザー情報と休憩データも一緒に取得
        // ユーザーが登録されていて打刻がない場合も考慮し、データが存在するもののみ取得
        $attendanceRecords = AttendanceRecord::with(['user', 'breakLogs'])
            ->where('date', $dateStr)
            ->get();

        // 管理者用勤怠一覧画面へ日付データと当日の勤怠データを渡して表示
        return view('admin.admin_attendance_list', [
            'date' => $date,
            'attendanceRecords' => $attendanceRecords,
        ]);
    }

    // 管理者用勤怠詳細画面を表示
    public function showDetail(int $id): View
    {
        // 指定した従業員ユーザーの勤怠データと休憩データを一緒に取得
        $attendance = AttendanceRecord::with(['user', 'breakLogs'])->findOrFail($id);

        // 管理者用勤怠詳細画面を返す
        return view('admin.admin_attendance_detail', [
            'attendance' => $attendance,
        ]);
    }
}
