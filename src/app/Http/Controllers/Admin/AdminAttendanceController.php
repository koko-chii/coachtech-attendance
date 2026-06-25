<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
// 別のURLへ移動するようブラウザへ返す
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use App\Models\AttendanceRecord;
use App\Http\Requests\AdminAttendanceUpdateRequest;
// 打刻修正申請データを管理するためのモデル
use App\Models\StampCorrectionRequest;
use App\Models\BreakLog;
use App\Models\User;

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

            // 承認待ちの修正申請データがある場合、修正値の出勤時刻・退勤時刻・備考を勤怠データに入れる
            if ($pendingData) {
                $attendance->setAttribute('clock_in', $pendingData->requested_clock_in);
                $attendance->setAttribute('clock_out', $pendingData->requested_clock_out);
                $attendance->setAttribute('remarks', $pendingData->requested_remarks);

                // 承認待ちの修正申請データに休憩時刻の修正値がある場合
                if (!empty($pendingData->requested_breaks)) {
                    $formattedBreaks = collect(array_values($pendingData->requested_breaks))->map(function ($b, $index) {
                    // 修正された休憩時刻をBreakLog形式に変換して返す
                    return new BreakLog([
                            'id' => $b['id'] ?? ($index + 1),
                            'break_in' => isset($b['break_in']) ? \Carbon\Carbon::parse($b['break_in'])->format('H:i') : null,
                            'break_out' => isset($b['break_out']) ? \Carbon\Carbon::parse($b['break_out'])->format('H:i') : null,
                        ]);
                    });
                    // 修正後の休憩データを、勤怠データに紐づく休憩情報としてセットする
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

        // 承認待ちの場合、修正不可のメッセージを画面へ返す
        if ($attendance->status === 'pending') {
            return redirect()->back()->with('alert_message', '承認待ちのため修正はできません。');
        }

        // 出勤時刻・退勤時刻・備考を更新して保存
        $attendance->update([
            'clock_in'  => $request->input('clock_in'),
            'clock_out' => $request->input('clock_out'),
            'remarks'   => $request->input('remarks'),
        ]);

        // 修正申請で送られら休憩データを取得
        $breaks = $request->input('breaks', []);

        // 休憩開始時刻または休憩終了時刻が入力されていた場合、休憩データに追加
        $newBreakIn = $request->input('new_break_in');
        $newBreakOut = $request->input('new_break_out');
        if ($newBreakIn || $newBreakOut) {
            $breaks[] = [
                'id' => null,
                'break_in' => $newBreakIn,
                'break_out' => $newBreakOut,
            ];
        }

        // 休憩データがある場合、既存の休憩データをIDごとに更新
        if (!empty($breaks)) {
            foreach ($breaks as $breakData) {
                // 休憩IDがある場合、既存の休憩データを取得
                if (!empty($breakData['id'])) {
                    $breakLog = $attendance->breakLogs()->find($breakData['id']);

                    // 既存の休憩データが見つかった場合は更新
                    if ($breakLog) {
                        $breakLog->update([
                            'break_in'  => $breakData['break_in'] ?? null,
                            'break_out' => $breakData['break_out'] ?? null,
                        ]);
                    }
                    // 既存の休憩データが見つからなかった場合は新しく作成
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

    //スタッフ一覧画面を表示するメソッド
    public function showStaffList(): View
    {
        //管理者ではないユーザー情報を取得
        $users = User::where('admin_status', false)->get();

        //管理者用スタッフ一覧画面を返す
        return view('admin.admin_staff_list', compact('users'));
    }

    //
    public function showStaffAttendance(Request $request, int $id): View
    {
        $targetUser = User::findOrFail($id);

        $currentMonthStr = $request->query('month', Carbon::now()->format('Y-m'));
        $currentMonth = Carbon::parse($currentMonthStr . '-01');

        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        $attendances = AttendanceRecord::with('breakLogs')
            ->where('user_id', $targetUser->id)
            ->whereYear('date', $currentMonth->year)
            ->whereMonth('date', $currentMonth->month)
            ->get()
            ->map(function (AttendanceRecord $record): AttendanceRecord {
                $record->display_clock_in = $record->clock_in;
                $record->display_clock_out = $record->clock_out;

                $totalBreakSeconds = $record->breakLogs->sum(function ($b): int {
                    if (!$b->break_in || !$b->break_out) return 0;
                    return Carbon::parse($b->break_in)->diffInSeconds(Carbon::parse($b->break_out));
                });

                $breakHours = floor($totalBreakSeconds / 3600);
                $breakMinutes = floor(($totalBreakSeconds % 3600) / 60);
                $record->display_break_time = $totalBreakSeconds > 0 ? sprintf('%02d:%02d', $breakHours, $breakMinutes) : '00:00';

                $record->display_work_time = '';
                if ($record->clock_in && $record->clock_out) {
                    $start = Carbon::parse($record->clock_in);
                    $end = Carbon::parse($record->clock_out);
                    $totalWorkSeconds = $start->diffInSeconds($end) - $totalBreakSeconds;

                    if ($totalWorkSeconds < 0) { $totalWorkSeconds = 0; }

                    $workHours = floor($totalWorkSeconds / 3600);
                    $workMinutes = floor(($totalWorkSeconds % 3600) / 60);
                    $record->display_work_time = sprintf('%02d:%02d', $workHours, $workMinutes);
                }

                return $record;
            })
            ->keyBy('date');

        $daysInMonth = [];
        $daysCount = $currentMonth->daysInMonth;
        for ($i = 1; $i <= $daysCount; $i++) {
            $daysInMonth[] = $currentMonth->copy()->day($i);
        }

        return view('admin.admin_staff_attendance', [
            'targetUser'   => $targetUser,
            'daysInMonth'  => $daysInMonth,
            'attendances'  => $attendances,
            'currentMonth' => $currentMonth->format('Y年m月'),
            'prevMonth'    => $prevMonth,
            'nextMonth'    => $nextMonth,
        ]);
    }

    public function downloadCsv(Request $request, int $id)
    {
        $targetUser = \App\Models\User::findOrFail($id);
        $currentMonthStr = $request->query('month', Carbon::now()->format('Y-m'));
        $currentMonth = Carbon::parse($currentMonthStr . '-01');

        $records = AttendanceRecord::with('breakLogs')
            ->where('user_id', $targetUser->id)
            ->whereYear('date', $currentMonth->year)
            ->whereMonth('date', $currentMonth->month)
            ->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $targetUser->name . '_' . $currentMonthStr . '_attendance.csv"',
        ];

        $callback = function () use ($records) {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, [mb_convert_encoding('日付', 'SJIS-win', 'UTF-8'), mb_convert_encoding('出勤', 'SJIS-win', 'UTF-8'), mb_convert_encoding('退勤', 'SJIS-win', 'UTF-8'), mb_convert_encoding('休憩時間', 'SJIS-win', 'UTF-8'), mb_convert_encoding('労働時間', 'SJIS-win', 'UTF-8')]);

            foreach ($records as $record) {
                $totalBreakSeconds = $record->breakLogs->sum(function ($b): int {
                    if (!$b->break_in || !$b->break_out) return 0;
                    return Carbon::parse($b->break_in)->diffInSeconds(Carbon::parse($b->break_out));
                });
                $breakTime = sprintf('%02d:%02d', floor($totalBreakSeconds / 3600), floor(($totalBreakSeconds % 3600) / 60));

                $workTime = '';
                if ($record->clock_in && $record->clock_out) {
                    $totalWorkSeconds = Carbon::parse($record->clock_in)->diffInSeconds(Carbon::parse($record->clock_out)) - $totalBreakSeconds;
                    if ($totalWorkSeconds < 0) $totalWorkSeconds = 0;
                    $workTime = sprintf('%02d:%02d', floor($totalWorkSeconds / 3600), floor(($totalWorkSeconds % 3600) / 60));
                }

                fputcsv($stream, [
                    $record->date,
                    $record->clock_in ? Carbon::parse($record->clock_in)->format('H:i') : '',
                    $record->clock_out ? Carbon::parse($record->clock_out)->format('H:i') : '',
                    $breakTime,
                    $workTime
                ]);
            }
            fclose($stream);
        };

        return response()->stream($callback, 200, $headers);
    }

        public function showRequestList(): View
    {
        $allRequests = StampCorrectionRequest::with(['user', 'attendanceRecord'])->get();

        $pendingRequests = $allRequests->filter(fn(StampCorrectionRequest $req): bool => $req->status === 'pending');
        $approvedRequests = $allRequests->filter(fn(StampCorrectionRequest $req): bool => $req->status === 'approved');

        return view('admin.admin_request_list', compact('pendingRequests', 'approvedRequests'));
    }

    public function showApproveView(int $id): View
    {
        $requestData = StampCorrectionRequest::with(['user', 'attendanceRecord'])->findOrFail($id);
        $isPending = $requestData->status === 'pending';

        return view('admin.admin_request_approve', compact('requestData', 'isPending'));
    }

    public function approveRequest(Request $request, int $id): RedirectResponse
    {
        $requestData = StampCorrectionRequest::findOrFail($id);
        $attendance = AttendanceRecord::findOrFail($requestData->attendance_record_id);

        if ($request->input('action') === 'approve') {
            $attendance->update([
                'clock_in'  => $requestData->requested_clock_in,
                'clock_out' => $requestData->requested_clock_out,
                'remarks'   => $requestData->requested_remarks,
            ]);

            if (!empty($requestData->requested_breaks)) {
            $attendance->breakLogs()->delete();
            collect($requestData->requested_breaks)->each(function (array $b) use ($attendance) {
                if (empty($b['break_in']) || empty($b['break_out'])) {
                    return;
                }
                $attendance->breakLogs()->create([
                    'break_in'  => $b['break_in'],
                    'break_out' => $b['break_out'],
                ]);
            });
        }

            $requestData->update(['status' => 'approved']);
            return redirect()->route('admin.request.list')->with('success_message', '申請を承認しました。');
        }

        $requestData->update(['status' => 'rejected']);
        return redirect()->route('admin.request.list')->with('success_message', '申請を却下しました。');
    }

}