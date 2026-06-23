<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use App\Models\AttendanceRecord;
use App\Http\Requests\AdminAttendanceUpdateRequest;

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
        $attendance = AttendanceRecord::with(['user', 'breakLogs', 'stampCorrectionRequests'])->findOrFail($id);

        // 承認待ち状態の申請データがあるか調べる
        $isPending = $attendance->stampCorrectionRequests ? $attendance->stampCorrectionRequests->contains('status', 'pending') : false;

        // もし承認待ち状態の申請データが存在すれば、管理者詳細画面の表示データを修正申請の値に書き換える
        if ($isPending) {
            $pendingData = $attendance->stampCorrectionRequests->where('status', 'pending')->first();
            
            if ($pendingData) {
                $attendance->setAttribute('clock_in', $pendingData->requested_clock_in);
                $attendance->setAttribute('clock_out', $pendingData->requested_clock_out);
                $attendance->setAttribute('remarks', $pendingData->requested_remarks);
                
                if (!empty($pendingData->requested_breaks)) {
                    $formattedBreaks = collect(array_values($pendingData->requested_breaks))->map(function ($b, $index) {
                        return new \App\Models\BreakLog([
                            'id' => $b['id'] ?? ($index + 1),
                            'break_in' => isset($b['break_in']) ? \Carbon\Carbon::parse($b['break_in'])->format('H:i') : null,
                            'break_out' => isset($b['break_out']) ? \Carbon\Carbon::parse($b['break_out'])->format('H:i') : null,
                        ]);
                    });
                    $attendance->setRelation('breakLogs', $formattedBreaks);
                }
            }
        }

        // 管理者用勤怠詳細画面を返す
        return view('admin.admin_attendance_detail', [
            'attendance' => $attendance,
            'isPending' => $isPending,
        ]);
    }

    // 管理者が勤怠修正を行った際の更新処理
    public function updateDetail(AdminAttendanceUpdateRequest $request, int $id): RedirectResponse
    {
        // 勤怠データと一緒に休憩データを取得
        $attendance = AttendanceRecord::with('breakLogs')->findOrFail($id);

        // 承認待ちの場合、修正不可のメッセージを表示して全画面へ戻る
        if ($attendance->status === 'pending') {
            return redirect()->back()->with('alert_message', '承認待ちのため修正はできません。');
        }

        // 出勤時刻・退勤時刻・備考を更新
        $attendance->update([
            'clock_in'  => $request->input('clock_in'),
            'clock_out' => $request->input('clock_out'),
            'remarks'   => $request->input('remarks'),
        ]);

        $breaks = $request->input('breaks', []);

        if ($request->filled('new_break_in') || $request->filled('new_break_out')) {
            $breaks[] = [
                'id' => null,
                'break_in' => $request->input('new_break_in'),
                'break_out' => $request->input('new_break_out'),
            ];
        }

        // 既存の休憩データを更新
        if (!empty($breaks)) {
            foreach ($breaks as $breakData) {
                // 対象の休憩IDがあるものだけ更新
                if (!empty($breakData['id'])) {
                    $breakLog = $attendance->breakLogs()->find($breakData['id']);

                    if ($breakLog) {
                        $breakLog->update([
                            'break_in'  => $breakData['break_in'] ?? null,
                            'break_out' => $breakData['break_out'] ?? null,
                        ]);
                    }
                } else {
                    $attendance->breakLogs()->create([
                        'break_in'  => $breakData['break_in'] ?? null,
                        'break_out' => $breakData['break_out'] ?? null,
                    ]);
                }
            }
        }

        // 修正完了メッセージと一緒に勤怠一覧画面へ遷移を返す
        return redirect()->route('admin.attendance.list')->with('success_message', '勤怠データを修正しました');
    }
}