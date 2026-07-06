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
        $attendanceRecords = AttendanceRecord::with(['user', 'breaks'])
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
        $attendance = AttendanceRecord::with(['user', 'breaks', 'applications'])->findOrFail($id);

        // 承認待ち状態の申請データがあるか調べる
        $isPending = $attendance->applications ? $attendance->applications->contains('status', 'pending') : false;

        // もし承認待ち状態の申請データが存在すれば、管理者詳細画面の表示データを修正申請の値に書き換える
        if ($isPending) {
            $pendingData = $attendance->applications->where('status', 'pending')->first();

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
        $attendance = AttendanceRecord::with('breaks')->findOrFail($id);

        // 承認待ちの場合、修正不可のメッセージを画面へ返す
        if ($attendance->status === 'pending') {
            return redirect()->back()->with('alert_message', '承認待ちのため修正はできません。');
        }

        // 出勤時刻・退勤時刻・備考を更新して保存
        $attendance->update([
            'clock_in'  => $request->input('clock_in'),
            'clock_out' => $request->input('clock_out'),
            'comment'   => $request->input('comment'),
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
            $attendance->breaks()->delete();
            foreach ($breaks as $breakData) {
                if (!empty($breakData['break_in'])) {
                    $attendance->breaks()->create([
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
        // 管理者ではないユーザー情報を取得
        $users = User::where('admin_status', false)->get();

        // 管理者用スタッフ一覧画面を返す
        return view('admin.admin_staff_list', compact('users'));
    }

    // スタッフ管理画面 指定したスタッフの勤怠一覧画面を表示
    public function showStaffAttendance(Request $request, int $id): View
    {
        // URLで指定したIDのスタッフデータを取得
        // 存在しないIDの場合は404エラーになる
        $targetUser = User::findOrFail($id);

        // 対象年月をY－m形式で取得 指定が無い場合は現在の年月を使用
        $currentMonthStr = $request->query('month', Carbon::now()->format('Y-m'));
        // 取得した年月の月初(1日)を生成
        $currentMonth = Carbon::parse($currentMonthStr . '-01');

        // 前月と翌月をY－m形式で取得
        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        // 勤怠データと一緒に休憩データを取得
        $attendances = AttendanceRecord::with('breaks')
            // 指定したIDのスタッフデータを検索
            ->where('user_id', $targetUser->id)
            // 更に対象年月データを検索し、取得
            ->whereYear('date', $currentMonth->year)
            ->whereMonth('date', $currentMonth->month)
            ->get()

            // 取得した勤怠データに表示用の出勤・退勤時刻を追加する
            ->map(function (AttendanceRecord $record): AttendanceRecord {
                $record->display_clock_in = $record->clock_in;
                $record->display_clock_out = $record->clock_out;

                // 休憩時刻の合計(秒)を計算
                $totalBreakSeconds = $record->breaks->sum(function ($b): int {
                    // もし休憩開始および休憩終了時刻が空の場合、0秒を返す
                    if (!$b->break_in || !$b->break_out) return 0;
                    // 休憩開始と休憩終了の差(秒)から休憩時刻を計算
                    return Carbon::parse($b->break_in)->diffInSeconds(Carbon::parse($b->break_out));
                });

                // 休憩時刻を秒から時間を計算(3600秒は1時間)
                $breakHours = floor($totalBreakSeconds / 3600);
                // 3600秒を60分に計算
                $breakMinutes = floor(($totalBreakSeconds % 3600) / 60);
                // 休憩時刻がある場合 時：分 形式で表示し、無い場合は 00:00 を表示
                $record->display_break_time = $totalBreakSeconds > 0 ? sprintf('%02d:%02d', $breakHours, $breakMinutes) : '00:00';

                // 退勤時刻－出勤時刻で勤務時間(秒)を計算
                $record->display_work_time = '';
                if ($record->clock_in && $record->clock_out) {
                    $start = Carbon::parse($record->clock_in);
                    $end = Carbon::parse($record->clock_out);
                    $totalWorkSeconds = $start->diffInSeconds($end) - $totalBreakSeconds;

                    // 勤務時間がマイナスになった場合は0秒にする
                    if ($totalWorkSeconds < 0) { $totalWorkSeconds = 0; }
                    // 秒を時間に変換し、勤務時間を計算
                    $workHours = floor($totalWorkSeconds / 3600);
                    $workMinutes = floor(($totalWorkSeconds % 3600) / 60);
                    $record->display_work_time = sprintf('%02d:%02d', $workHours, $workMinutes);
                }
                // 加工した勤務データを返す
                return $record;
            })
            // 日付をキーにしてデータを取得しやすくする
            ->keyBy('date');

        // 対象月の日付一覧を作成
        $daysInMonth = [];
        // 1日から月末までの日付を順番に追加
        $daysCount = $currentMonth->daysInMonth;
        for ($i = 1; $i <= $daysCount; $i++) {
            $daysInMonth[] = $currentMonth->copy()->day($i);
        }

        // 指定したスタッフデータ・対象月の日付一覧・勤怠データ・年月データ形式変換・前月・翌月データをビューへ渡す
        return view('admin.admin_staff_attendance', [
            'targetUser'   => $targetUser,
            'daysInMonth'  => $daysInMonth,
            'attendances'  => $attendances,
            'currentMonth' => $currentMonth->format('Y年m月'),
            'prevMonth'    => $prevMonth,
            'nextMonth'    => $nextMonth,
        ]);
    }

    // Csv出力処理
    public function downloadCsv(Request $request, int $id)
    {
        // 指定したスタッフデータを取得
        $targetUser = User::findOrFail($id);
        // URLで指定が無い場合、現在の年月を設定
        $currentMonthStr = $request->query('month', Carbon::now()->format('Y-m'));
        // 指定月の月初の日付を生成
        $currentMonth = Carbon::parse($currentMonthStr . '-01');

        // 指定したスタッフ・年月日の勤怠データと一緒に休憩データを取得
        $records = AttendanceRecord::with('breakLogs')
            ->where('user_id', $targetUser->id)
            ->whereYear('date', $currentMonth->year)
            ->whereMonth('date', $currentMonth->month)
            ->get();

        // スタッフ名と指定年月を含むｃｓｖファイル名を設定
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $targetUser->name . '_' . $currentMonthStr . '_attendance.csv"',
        ];

        // Csvに出力するデータを作成
        $callback = function () use ($records) {
            // 出力先を準備
            $stream = fopen('php://output', 'w');
            // csvヘッダーの作成
            fputcsv($stream, [mb_convert_encoding('日付', 'SJIS-win', 'UTF-8'), mb_convert_encoding('出勤', 'SJIS-win', 'UTF-8'), mb_convert_encoding('退勤', 'SJIS-win', 'UTF-8'),
            mb_convert_encoding('休憩時間', 'SJIS-win', 'UTF-8'), mb_convert_encoding('労働時間', 'SJIS-win', 'UTF-8')]);
            // 計勤怠データごとに休憩時間の計算
            foreach ($records as $record) {
                $totalBreakSeconds = $record->breakLogs->sum(function ($b): int {
                    if (!$b->break_in || !$b->break_out) return 0;
                    // carbon形式に変換して(文字列を日時を扱えるよう変換)csv出力
                    return Carbon::parse($b->break_in)->diffInSeconds(Carbon::parse($b->break_out));
                });
                $breakTime = sprintf('%02d:%02d', floor($totalBreakSeconds / 3600), floor(($totalBreakSeconds % 3600) / 60));

                // 労働時間の計算し、秒から時：分形式へ変換（労働時間がマイナスの場合は00:00）
                $workTime = '';
                if ($record->clock_in && $record->clock_out) {
                    $totalWorkSeconds = Carbon::parse($record->clock_in)->diffInSeconds(Carbon::parse($record->clock_out)) - $totalBreakSeconds;
                    if ($totalWorkSeconds < 0) $totalWorkSeconds = 0;
                    $workTime = sprintf('%02d:%02d', floor($totalWorkSeconds / 3600), floor(($totalWorkSeconds % 3600) / 60));
                }

                // 日付、出勤退勤時刻、休憩時間、労働時間をcsvへ出力
                fputcsv($stream, [
                    $record->date,
                    $record->clock_in ? Carbon::parse($record->clock_in)->format('H:i') : '',
                    $record->clock_out ? Carbon::parse($record->clock_out)->format('H:i') : '',
                    $breakTime,
                    $workTime
                ]);
            }
            // csvの出力終了
            fclose($stream);
        };

        // csvファイルを正常に返す(ダウンロード)
        return response()->stream($callback, 200, $headers);
    }

    // 申請一覧画面の処理
    public function showRequestList(): View
    {
        // 修正申請データと一緒にスタッフデータと勤怠データを取得
        $allRequests = StampCorrectionRequest::with(['user', 'attendanceRecord'])->get();

        // 承認待ちと承認済みデータを振り分け、部屋番号を0から詰め直す
        $pendingRequests = $allRequests->filter(fn(StampCorrectionRequest $req): bool => $req->status === 'pending')->values();
        $approvedRequests = $allRequests->filter(fn(StampCorrectionRequest $req): bool => $req->status === 'approved')->values();

        // 管理者用申請一覧へ表示
        return view('admin.admin_request_list', compact('pendingRequests', 'approvedRequests'));
    }

    // 修正申請の承認画面の処理
    public function showApproveView(int $id): View
    {
        // 修正申請データと一緒にスタッフデータと勤怠データを1件だけ取得
        $requestData = StampCorrectionRequest::with(['user', 'attendanceRecord'])->findOrFail($id);
        // 承認待ちかを判定
        $isPending = $requestData->status === 'pending';

        // 管理者用修正申請の承認画面を表示
        return view('admin.admin_request_approve', compact('requestData', 'isPending'));
    }

    // 承認ボタンを押した後の処理
    public function approveRequest(Request $request, int $id): RedirectResponse
    {
        // 修正申請データを1件だけ取得
        $requestData = StampCorrectionRequest::findOrFail($id);
        // 勤怠データから修正データを取得
        $attendance = AttendanceRecord::findOrFail($requestData->attendance_record_id);

        // 承認ボタンを押したら承認済みにし、データを更新する
        if ($request->input('action') === 'approve') {
            $attendance->update([
                'clock_in'  => $requestData->requested_clock_in,
                'clock_out' => $requestData->requested_clock_out,
                'comment'   => $requestData->requested_comment,
            ]);

            // もし修正データに休憩修正がある場合、既存データを削除
            if (!empty($requestData->requested_breaks)) {
                $attendance->breaks()->delete();
                // 新しい休憩データを1件ずつ作成
                collect($requestData->requested_breaks)->each(function (array $b) use ($attendance) {
                    // 空データをスキップ
                    if (empty($b['break_in']) || empty($b['break_out'])) {
                        return;
                    }
                    // データベースの休憩データを新しく作る
                    $attendance->breaks()->create([
                        'break_in'  => $b['break_in'],
                        'break_out' => $b['break_out'],
                    ]);
                });
            }

            // ステータスを承認済みに更新
            $requestData->update(['status' => 'approved']);
            // 管理者用申請一覧の「承認済み」タブへ遷移と一緒に、メッセージを返す
            return redirect()->route('admin.request.list')->with('success_message', '申請を承認しました。');
        }
    }
}


