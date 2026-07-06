<?php

namespace App\Http\Controllers;

// リクエスト機能を使うための呼び出し
use Illuminate\Http\Request;
// 認証機能を使うための呼び出し
use Illuminate\Support\Facades\Auth;
// 現在の時刻の取得や、勤務時間の計算機能を使うための呼び出し
use Carbon\Carbon;
// 勤務時間の打刻機能を使うための呼び出し
use App\Models\AttendanceRecord;
// Laravel標準のView機能を使うための読み込み
use Illuminate\Contracts\View\View;
// データーベースの休憩データーを操作する休憩情報モデルを使うための読み込み
use App\Models\BreakLog;
// laravel標準の画面切り替え機能を使うための読み込み
use Illuminate\Http\RedirectResponse;
// 修正申請データを操作するモデルの読み込み
use App\Models\StampCorrectionRequest;
// 修正申請データーを操作するモデルの読み込み
use App\Http\Requests\UpdateAttendanceRequest;

// 勤怠管理コントローラーを作成するためのクラス(設置)
class AttendanceController extends Controller
{
    // 勤怠管理画面を表示するための関数(機能)
    public function index(): View
    {
        // ログイン済みユーザー情報を取得し、user変数(箱)に入れる
        $user = Auth::user();
        // 今日の日付情報を取得し、today変数(箱)に入れる
        $today = Carbon::today();

        // ユーザー情報と出勤データーを探し、勤怠管理変数(箱)に入れる
        $attendance = AttendanceRecord::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        // 勤怠データに紐づく修正申請データ
        $correctionRequest = $attendance
            ? StampCorrectionRequest::where('attendance_record_id', $attendance->id)->latest()->first()
            : null;

        // 休憩中ではないフラグ状態を変数(箱)に入れる
        $is_breaking = false;
        // 退勤済みではないフラグ状態を変数(箱)に入れる
        $is_clocked_out = false;

        // 出退勤情報と休憩中の情報を取得し、休憩戻りがまだか調べる
        // (直訳)もし出勤していて退勤していなければ
        // 勤怠管理データーから探し出し休憩から戻ってないか調べる。
        if ($attendance) {
            // もし既に退勤していたら退勤済みフラグをtrue(真)にする
            if ($attendance->clock_out) {
                $is_clocked_out = true;
            // 休憩中なら休憩終了が空か判定
            } else {
                $is_breaking = $attendance->breaks()
                    ->whereNull('break_out')
                    ->exists();
            }
        }

        // 出勤情報と休憩中情報、今日の日付と表示方法、退勤済み情報を箱にしまい、
        // 勤怠管理画面に表示する
        return view('attendance', [
            'attendance'     => $attendance,
            'correctionRequest' => $correctionRequest,
            'is_breaking'    => $is_breaking,
            'is_clocked_out' => $is_clocked_out,
            'today'          => $today->format('Y年n月j日'),
        ]);
    }

    // 出勤ボタンが押されたときの関数(機能)
    public function clockIn(): RedirectResponse
    {
        // ログインしているユーザー情報をuser変数(箱)にしまう
        $user = Auth::user();
        // 今日の日時情報を取得計算し、today変数(箱)にしまう
        $today = Carbon::today();

        // 今日出勤しているユーザーデータを調べexists変数(箱)にしまう(重複を防ぐため)
        $exists = AttendanceRecord::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->exists();

        // もし出勤していなかったらユーザー情報と出勤日時を日付形式にして新たに作成する
        if (!$exists) {
            AttendanceRecord::create([
                'user_id'  => $user->id,
                'date'     => $today->toDateString(),
                'clock_in' => Carbon::now()->toTimeString(),
            ]);
        }

        // 元の画面に戻す
        return redirect()->back();
    }

    // 退勤ボタンが押されたときの関数(機能)
    public function clockOut(): RedirectResponse
    {
        // ログインしているユーザー情報をuser変数(箱)にいれる
        $user = Auth::user();
        // 今日の日時情報を取得し、today変数(箱)にいれる
        $today = Carbon::today();

        // 今日出勤しているユーザーデーターを1つ探しattendance変数(箱)にいれる
        $attendance = AttendanceRecord::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        // もし出勤していて退勤がまだなら退勤日時を日付形式にして更新する
        if ($attendance && !$attendance->clock_out) {
            $attendance->update([
                'clock_out' => Carbon::now()->toTimeString(),
            ]);

            // 元の画面に戻す（仕様書通りのメッセージをセッションに添えて返す）
            return redirect()->back()->with('message', 'お疲れ様でした。');
        }

        // 元の画面に戻す
        return redirect()->back();
    }

   // 休憩ボタンが押されたときの関数(機能)
    public function break(): \Illuminate\Http\RedirectResponse
    {
        // ログインしているユーザーをuser変数(箱)に入れる
        $user = Auth::user();
        // 今日の日付を取得してtoday変数(箱)にいれる
        $today = Carbon::today();
        // 今現在の日付と正確な時刻を取得してnow変数(箱)に入れる
        $now = Carbon::now();

        // 今日出勤しているユーザー情報を1つ探し出す
        $attendance = AttendanceRecord::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        // 出勤していない、または既に退勤している場合は戻し返す
        if (!$attendance || $attendance->clock_out) {
            return redirect()->back();
        }

        // 休憩ボタンが押され休憩戻はまだの情報が1件でもあるか探し出す
        $activeBreak = $attendance->breaks()
            ->whereNull('break_out')
            ->first();


        // もしたくさんの休憩情報のなかに休憩中を見つけたらactiveBreak変数(箱)へしまう
        // 休憩戻を押したら、now変数(箱)のデータを現在の時刻形式で更新する
        if ($activeBreak) {
            $activeBreak->update([
                'break_out'  => $now->toTimeString(),
                'updated_at' => $now,
            ]);
        }
        // 休憩中情報が無かったら、新しく休憩中テーブル
        // (勤怠管理情報、休憩入時刻形式情報、新規休憩入り情報、更新情報)を登録する
        else {
            $attendance->breaks()->create([
                'break_in'   => $now->toTimeString(),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
        // 元の画面に戻す
        return redirect()->back();
    }

    // 勤怠一覧画面でユーザーから送られてきたリクエストを実行するための関数(機能)
    public function showList(Request $request): View
    {
        // ログインユーザーの情報を取得
        $user = Auth::user();

        // ユーザーが指定した年月を取得
        $currentMonthStr = $request->query('month', Carbon::now()->format('Y-m'));
        // 取得した年月の1日を取得
        $currentMonth = Carbon::parse($currentMonthStr . '-01');

        // ユーザーが指定した年月の前月の情報を取得し、変数(箱)prevMonthにしまう
        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        // ユーザーが指定した年月の翌月情報を取得し、変数(箱)nextMonthにしまう
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        // 勤怠登録データーからこのユーザー情報を探す(休憩データも一緒に取得N+1問題防止)
        $attendances = AttendanceRecord::with('breaks')
            ->where('user_id', $user->id)
            ->whereYear('date', $currentMonth->year)
            ->whereMonth('date', $currentMonth->month)
            ->get()
            ->map(function (AttendanceRecord $record): AttendanceRecord {
                // 承認されるまでは元の確定データのみを使って計算する
                // テストコードが直接参照する元のカラム名に値を確実にセットする
                $record->clock_in = $record->clock_in;
                $record->clock_out = $record->clock_out;

                // Collectionメソッドを使って休憩時間の合計秒数を計算（N+1問題を防止）
                $totalBreakSeconds = $record->breaks->sum(function (BreakLog $b): int {
                    if (!$b->break_in || !$b->break_out) return 0;
                    return Carbon::parse($b->break_in)->diffInSeconds(Carbon::parse($b->break_out));
                });

                // 合計休憩時間を秒計算し、何時間何分に変換し変数(箱)にしまう
                $breakHours = floor($totalBreakSeconds / 3600);
                $breakMinutes = floor(($totalBreakSeconds % 3600) / 60);
                $record->display_break_time = $totalBreakSeconds > 0 ? sprintf('%02d:%02d', $breakHours, $breakMinutes) : '00:00';

                // もし出勤記録があれば(退勤時間-出勤時間-休憩時間）勤務秒数を計算
                $record->display_work_time = '';
                if ($record->clock_in && $record->clock_out) {
                    $start = Carbon::parse($record->clock_in);
                    $end = Carbon::parse($record->clock_out);
                    $totalWorkSeconds = $start->diffInSeconds($end) - $totalBreakSeconds;

                    if ($totalWorkSeconds < 0) { $totalWorkSeconds = 0; }

                    // 合計勤務秒数を何時間何分に変換し変数(箱)にしまう
                    $workHours = floor($totalWorkSeconds / 3600);
                    $workMinutes = floor(($totalWorkSeconds % 3600) / 60);
                    $record->display_work_time = sprintf('%02d:%02d', $workHours, $workMinutes);
                }
                // 計算した勤務時間を返す
                return $record;
            })
            // 返されたrecordをデータキーにして、勤怠データを扱いやすくする
            ->keyBy('date');

        // 日付を昇順に並べる
        $daysInMonth = [];
        $daysCount = $currentMonth->daysInMonth;
        for ($i = 1; $i <= $daysCount; $i++) {
            $daysInMonth[] = $currentMonth->copy()->day($i);
        }

        // 勤怠一覧画面を表示する
        return view('attendance_list', [
            'daysInMonth'  => $daysInMonth,
            'attendances'  => $attendances,
            'currentMonth' => $currentMonth->format('Y年m月'),
            'prevMonth'    => $prevMonth,
            'nextMonth'    => $nextMonth,
        ]);
    }

    // 勤怠詳細画面を表示するための関数(機能)
    public function show(int $id): View
    {
        // ログインユーザー情報から勤怠登録データと一緒に休憩データを持ってきて変数(箱)にしまう
        $record = AttendanceRecord::where('user_id', auth()->id())
            ->findOrFail($id);

        // 勤怠データ1件に関連する休憩情報と申請情報を取得
        $record->load(['breaks', 'applications']);

        // 承認待ち状態の申請データがあるか調べる
        $isPending = $record->applications ? $record->applications->contains('status', 'pending') : false;
        // 承認待ち、または承認済みの最新申請データを取得して変数に代入
        $correctionRequest = $record->applications ? $record->applications->sortByDesc('created_at')->first() : null;

        // 承認待ちデータがある場合、
        if ($isPending) {
            $pendingData = $record->applications->where('status', 'pending')->first();
            $correctionRequest = $pendingData;

            // 出勤時刻・退勤時刻・備考を勤怠データに上書き
            if ($pendingData) {
                $record->clock_in = Carbon::parse($pendingData->requested_clock_in)->format('H:i');
                $record->clock_out = $pendingData->requested_clock_out ? Carbon::parse($pendingData->requested_clock_out)->format('H:i') : null;
                $record->comment = $pendingData->comment;

                // 修正申請に休憩データがある場合
                if (!empty($pendingData->requested_breaks)) {
                    $formattedBreaks = collect(array_values($pendingData->requested_breaks))->map(function ($b, $index) {

                    // 休憩データ1件を表すものを作成して返す
                        return new BreakLog([
                            'id' => $b['id'] ?? ($index + 1),
                            'break_in' => isset($b['break_in']) ? Carbon::parse($b['break_in'])->format('H:i') : null,
                            'break_out' => isset($b['break_out']) ? Carbon::parse($b['break_out'])->format('H:i') : null,
                        ]);
                    });
                    // 休憩データを1件の勤怠データに紐づける
                    $record->setRelation('breaks', $formattedBreaks);
                }
            }
        }

        // 承認済み、かつ過去の申請データが存在する場合、勤怠データの備考に上書き
        if (!$isPending && $correctionRequest && $correctionRequest->comment) {
            $record->comment = $correctionRequest->comment;
        }

        // 勤怠詳細画面を表示する
        return view('attendance_detail', compact('record', 'isPending', 'correctionRequest'));
    }

    // 修正ボタン押下後の画面切り替え機能を使うための関数(機能)
    public function update(UpdateAttendanceRequest $request, int $id): RedirectResponse
    {
        // 備考欄の未入力チェック、出勤・退勤時間の前後関係の入力チェックを行う
        $request->validate([
            'comment'   => ['required'],
            'clock_in'  => ['required'],
            'clock_out' => ['required', 'after:clock_in'],
        ], [
            'comment.required' => '備考を記入してください',
            'clock_out.after'  => '出勤時間もしくは退勤時間が不適切な値です',
        ]);

        // もし休憩データの修正がある場合、退勤時間より後になっていないか不適切な値をチェック
        if ($request->has('breaks')) {
            foreach ($request->input('breaks') as $breakId => $breakData) {
                // 安全対策：データが存在するときだけ比較を行うように判定（isset）を追加
                if (isset($breakData['break_in']) && $breakData['break_in'] > $request->input('clock_out')) {
                    return back()->withErrors(['breaks.' . $breakId . '.break_in' => '休憩時間が不適切な値です']);
                }
                if (isset($breakData['break_out']) && $breakData['break_out'] > $request->input('clock_out')) {
                    return back()->withErrors(['breaks.' . $breakId . '.break_out' => '休憩時間もしくは退勤時間が不適切な値です']);
                }
            }
        }

        // 勤怠登録からユーザーの修正する情報を見つける
        $record = AttendanceRecord::findOrFail($id);

        // 休憩データを取得 なければ空配列
        $breaks = $request->input('breaks', []);

        // 新しい休憩時間が入力されているか確認
        if ($request->filled('new_break_in') || $request->filled('new_break_out')) {

            // 新しい休憩データを配列の最後に追加
            $breaks[] = [
                'break_in'  => $request->input('new_break_in'),
                'break_out' => $request->input('new_break_out'),
            ];
        }

        // 元の勤怠は書き換えず、勤怠修正申請データを承認待ちとして作成する
        StampCorrectionRequest::create([
            'user_id'              => auth()->id(),
            'attendance_record_id' => $record->id,
            'requested_clock_in'   => $request->input('clock_in'),
            'requested_clock_out'  => $request->input('clock_out'),
            'requested_breaks'     => $breaks,
            'status'               => 'pending',
            'comment' => $request->input('comment'),
        ]);

        // 申請一覧画面へ遷移する
        return redirect('/stamp_correction_request/list')->with('success_message', '修正を申請しました');

    }

    // レポートを表示するための処理
    public function report(Request $request): View
    {
        // スタッフの認証チェック
        $user = Auth::user();
        // 現在日時を取得
        $now = Carbon::now();
        // 現在から6か月前の月初の日付を取得
        $sixMonthsAgo = $now->copy()->subMonths(5)->startOfMonth();

        // 1件の勤怠データと一緒に休憩データを取得
        $records = AttendanceRecord::with('breaks')
            ->where('user_id', $user->id)
            ->where('date', '>=', $sixMonthsAgo->format('Y-m-d'))
            ->where('date', '<=', $now->format('Y-m-d'))
            ->get();

        // 月ごとの集計データを保存
        $monthlyData = collect();
        for ($i = 5; $i >= 0; $i--) {
            $monthStr = $now->copy()->subMonths($i)->format('Y-m');

            // 指定した年月の勤怠データだけ取得
            $monthRecords = $records->filter(fn($r) => Carbon::parse($r->date)->format('Y-m') === $monthStr);

            // 出勤時刻または退勤時刻が無い場合、0を返す
            $totalWorkSeconds = $monthRecords->sum(function ($r) {
                if (!$r->clock_in || !$r->clock_out) {
                    return 0;
                }
                // 出退勤時刻を文字列からにcarbonに変換し秒単位で計算
                $staySeconds = Carbon::parse($r->clock_in)->diffInSeconds(Carbon::parse($r->clock_out));
                // 休憩開始時刻または休憩終了時刻が無い場合、0を返す
                $breakSeconds = $r->breaks->sum(function ($b) {
                    if (!$b->break_in || !$b->break_out) {
                        return 0;
                    }
                    // 休憩時刻をcarbon形式に変換し秒単位で計算して返す
                    return Carbon::parse($b->break_in)->diffInSeconds(Carbon::parse($b->break_out));
                });
                // 休憩時間を差し引いた労働時間を0未満にならないようにして返す
                return max(0, $staySeconds - $breakSeconds);
            });

            // 月ごとの残業時間を秒単位で計算し、出退勤時刻が無い場合、0を返す
            $totalOvertimeSeconds = $monthRecords->sum(function ($r) {
                if (!$r->clock_in || !$r->clock_out) {
                    return 0;
                }
                // 出退勤時刻をcarbonに変換し、秒単位で計算
                $staySeconds = Carbon::parse($r->clock_in)->diffInSeconds(Carbon::parse($r->clock_out));
                // 休憩開始時刻または休憩終了時刻が無い場合、0を返す
                $breakSeconds = $r->breaks->sum(function ($b) {
                    if (!$b->break_in || !$b->break_out) {
                        return 0;
                    }
                    // 休憩時間をcarbon形式に変換し、秒単位で計算して返す
                    return Carbon::parse($b->break_in)->diffInSeconds(Carbon::parse($b->break_out));
                });
                // 休憩時間を差し引いた労働時間を0未満にならないようにして秒で取得
                $workSeconds = max(0, $staySeconds - $breakSeconds);
                // 8時間を超えた分を残業時間として返す
                return max(0, $workSeconds - 28800);
            });

            // 月ごとの勤務時間・残業時間・勤務秒数を保存
            $monthlyData->put($monthStr, [
                'work_hours' => (int)floor($totalWorkSeconds / 3600),
                'work_minutes' => (int)floor(($totalWorkSeconds % 3600) / 60),
                'overtime_hours' => (int)floor($totalOvertimeSeconds / 3600),
                'overtime_minutes' => (int)floor(($totalOvertimeSeconds % 3600) / 60),
                'raw_work_seconds' => $totalWorkSeconds,
            ]);
        }

        // 6か月分の総勤務時間を秒で集計
        $grandTotalWorkSeconds = $monthlyData->sum('raw_work_seconds');

        // 出勤時刻または退勤時刻が無い場合、0を返す
        $grandTotalOvertimeSeconds = $records->sum(function ($r) {
            if (!$r->clock_in || !$r->clock_out) {
                return 0;
            }
            // 出勤時刻と退勤時刻をcarbonに変換し、秒計算
            $staySeconds = Carbon::parse($r->clock_in)->diffInSeconds(Carbon::parse($r->clock_out));
            // 休憩時間を秒で取得
            $breakSeconds = $r->breaks->sum(function ($b) {
                // 休憩開始時刻または休憩終了時刻が無い場合、0で返す
                if (!$b->break_in || !$b->break_out) {
                    return 0;
                }
                // 休憩開始時刻と休憩終了時刻をcarbon形式で取得し、秒計算
                return Carbon::parse($b->break_in)->diffInSeconds(Carbon::parse($b->break_out));
            });
            // 休憩時間を差し引いた労働時間を0未満にならないようにして秒単位で返す
            $workSeconds = max(0, $staySeconds - $breakSeconds);
            return max(0, $workSeconds - 28800);
        });

        // 総勤務日数を数える
        $totalDays = $records->count();
        // 勤務日数が0より多い場合は平均勤務時間を計算し、それ以外は0を設定
        $averageWorkSeconds = $totalDays > 0 ? (int)round($grandTotalWorkSeconds / $totalDays) : 0;

        // 総勤務時間・総残業時間・平均勤務時間を計算
        $summary = [
            'total_work' => ['h' => (int)floor($grandTotalWorkSeconds / 3600), 'm' => (int)floor(($grandTotalWorkSeconds % 3600) / 60)],
            'total_overtime' => ['h' => (int)floor($grandTotalOvertimeSeconds / 3600), 'm' => (int)floor(($grandTotalOvertimeSeconds % 3600) / 60)],
            'average_work' => ['h' => (int)floor($averageWorkSeconds / 3600), 'm' => (int)floor(($averageWorkSeconds % 3600) / 60)],
        ];

        // 現在年月を取得
        $currentMonthStr = $now->format('Y-m');
        // 日付をcarbonに変換し、年月に変換して当月の勤怠データを抽出
        $currentMonthRecords = $records->filter(fn($r) => Carbon::parse($r->date)->format('Y-m') === $currentMonthStr);

        $anomaly = [
            // 出勤時間をcarbonに変換し、9時より遅い場合は遅刻で数える
            'lateness' => $currentMonthRecords->filter(fn($r) => Carbon::parse($r->clock_in)->format('H:i:s') > '09:00:00')->count(),
            // 退勤時間をcarbonに変換し、18時より早い場合は早退で数える
            'early_leave' => $currentMonthRecords->filter(fn($r) => $r->clock_out && Carbon::parse($r->clock_out)->format('H:i:s') < '18:00:00')->count(),
            // 長時間労働の勤怠を数える
            'long_working' => $currentMonthRecords->filter(function ($r) {
                // 出勤時刻または退勤時刻が無い場合、除外する
                if (!$r->clock_in || !$r->clock_out) {
                    return false;
                }
                // 出勤時刻と退勤時刻をcarbonに変換し秒計算
                $staySeconds = Carbon::parse($r->clock_in)->diffInSeconds(Carbon::parse($r->clock_out));
                // 休憩時間を秒計算
                $breakSeconds = $r->breaks->sum(function ($b) {
                    // 休憩開始または休憩終了時刻が無い場合、0を返す
                    if (!$b->break_in || !$b->break_out) {
                        return 0;
                    }
                    // 休憩時刻をcarobnに変換して秒で返す
                    return Carbon::parse($b->break_in)->diffInSeconds(Carbon::parse($b->break_out));
                });
                // 勤務時間が10時間を超えるか判定して数える
                return ($staySeconds - $breakSeconds) > 36000;
            })->count(),
        ];
        //  総勤務時間・総残業時間・平均勤務時間・遅刻回数。早退回数・長時間労働の件数・月ごとの総勤務集計をビューへ返す
        return view('attendance_report', compact('summary', 'monthlyData', 'anomaly'));
    }
}
