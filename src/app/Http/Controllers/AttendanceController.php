<?php

namespace App\Http\Controllers;

//リクエスト機能を使うための呼び出し
use Illuminate\Http\Request;
//認証機能を使うための呼び出し
use Illuminate\Support\Facades\Auth;
//現在の時刻の取得や、勤務時間の計算機能を使うための呼び出し
use Carbon\Carbon;
//勤務時間の打刻機能を使うための呼び出し
use App\Models\AttendanceRecord;
//Laravel標準のView機能を使うための読み込み
use Illuminate\Contracts\View\View;
//データーベースの休憩データーを操作する休憩情報モデルを使うための読み込み
use App\Models\BreakLog;
//laravel標準の画面切り替え機能を使うための読み込み
use Illuminate\Http\RedirectResponse;
//修正申請データを操作するモデルの読み込み
use App\Models\StampCorrectionRequest;
//修正申請データーを操作するモデルの読み込み
use App\Http\Requests\UpdateAttendanceRequest;

//勤怠管理コントローラーを作成するためのクラス(設置)
class AttendanceController extends Controller
{
    //勤怠管理画面を表示するための関数(機能)
    public function index(): View
    {
        //ログイン済みユーザー情報を取得し、user変数(箱)に入れる
        $user = Auth::user();
        //今日の日付情報を取得し、today変数(箱)に入れる
        $today = Carbon::today();

        //ユーザー情報と出勤データーを探し、勤怠管理変数(箱)に入れる
        $attendance = AttendanceRecord::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        // 休憩中ではないフラグ状態を変数(箱)に入れる
        $is_breaking = false;

        //出退勤情報と休憩中の情報を取得し、休憩戻りがまだか調べる
        //(直訳)もし出勤していて退勤していなければ
        // 勤怠管理データーから探し出し休憩から戻ってないか調べる。　
        if ($attendance && !$attendance->clock_out) {
            $is_breaking = BreakLog::where('attendance_record_id', $attendance->id)
                ->whereNull('break_out')
                ->exists();
        }


        //出勤情報と休憩中情報、今日の日付と表示方法をを箱にしまい、
        // 勤怠管理画面に表示する
        return view('attendance', [
            'attendance'  => $attendance,
            'is_breaking' => $is_breaking,
            'today'       => $today->format('Y年n月j日'),
        ]);
    }

    //出勤ボタンが押されたときの関数(機能)
    public function clockIn(): RedirectResponse
    {
        //ログインしているユーザー情報をuser変数(箱)にしまう
        $user = Auth::user();
        //今日の日時情報を取得計算し、today変数(箱)にしまう
        $today = Carbon::today();

        //今日出勤しているユーザーデータを調べexists変数(箱)にしまう(重複を防ぐため)
        $exists = AttendanceRecord::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->exists();

        //もし出勤していなかったらユーザー情報と出勤日時を日付形式にして新たに作成する
        if (!$exists) {
            AttendanceRecord::create([
                'user_id'  => $user->id,
                'date'     => $today->toDateString(),
                'clock_in' => Carbon::now()->toTimeString(),
            ]);
        }

        //元の画面に戻す
        return redirect()->back();
    }

    //退勤ボタンが押されたときの関数(機能)
    public function clockOut(): RedirectResponse
    {
        //ログインしているユーザー情報をuser変数(箱)にいれる
        $user = Auth::user();
        //今日の日時情報を取得し、today変数(箱)にいれる
        $today = Carbon::today();

        //今日出勤しているユーザーデーターを1つ探しattendance変数(箱)にいれる
        $attendance = AttendanceRecord::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        //もし出勤していて退勤がまだなら退勤日時を日付形式にして更新する
        if ($attendance && !$attendance->clock_out) {
            $attendance->update([
                'clock_out' => Carbon::now()->toTimeString(),
            ]);
        }

        //元の画面に戻す
        return redirect()->back();
    }

    //休憩ボタンが押されたときの関数(機能)
    public function break(): RedirectResponse
    {
        //ログインしているユーザーをuser変数(箱)に入れる
        $user = Auth::user();
        //今日の日付を取得してtoday変数(箱)にいれる
        $today = Carbon::today();
        //今現在の日付と正確な時刻を取得してnow変数(箱)に入れる
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
        $activeBreak = BreakLog::where('attendance_record_id', $attendance->id)
            ->whereNull('break_out')
            ->first();


        //もしたくさんの休憩情報のなかに休憩中を見つけたらactiveBreak変数(箱)へしまう
        //休憩戻を押したら、now変数(箱)のデータを現在の時刻形式で更新する
        if ($activeBreak) {
            $activeBreak->update([
                'break_out'  => $now->toTimeString(),
                'updated_at' => $now,
            ]);
        }
        //休憩中情報が無かったら、新しく休憩中テーブル
        //(勤怠管理情報、休憩入時刻形式情報、新規休憩入り情報、更新情報)を登録する
        else {
            BreakLog::create([
                'attendance_record_id' => $attendance->id,
                'break_in'             => $now->toTimeString(),
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);
        }
        //元の画面に戻す
        return redirect()->back();
    }

    //勤怠一覧画面でユーザーから送られてきたリクエストを実行するための関数(機能)
    public function showList(Request $request): View
    {
        //ログインユーザーの情報を取得
        $user = Auth::user();

        //ユーザーが指定した年月を取得
        $currentMonthStr = $request->query('month', Carbon::now()->format('Y-m'));
        //取得した年月の1日を取得
        $currentMonth = Carbon::parse($currentMonthStr . '-01');

        //ユーザーが指定した年月の前月の情報を取得し、変数(箱)prevMonthにしまう
        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        //ユーザーが指定した年月の翌月情報を取得し、変数(箱)nextMonthにしまう
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        //勤怠登録データーからこのユーザー情報を探す(休憩データも一緒に取得N+1問題防止)
        //ユーザーが指定した年月データーを取ってきて
        // 日付をキー(見出し)にして箱(attendances)に整理する
        $attendances = AttendanceRecord::with('breakLogs')
            ->where('user_id', $user->id)
            ->whereYear('date', $currentMonth->year)
            ->whereMonth('date', $currentMonth->month)
            ->get()
            ->keyBy('date');

        //日付を昇順に並べる
        $daysInMonth = [];
        $daysCount = $currentMonth->daysInMonth;
        for ($i = 1; $i <= $daysCount; $i++) {
            $daysInMonth[] = $currentMonth->copy()->day($i);
        }

        //勤怠一覧画面を表示する
        //(ユーザーがして指定した年月、日付け一覧、前月、翌月)
        return view('attendance_list', [
            'daysInMonth'  => $daysInMonth,
            'attendances'  => $attendances,
            'currentMonth' => $currentMonth->format('Y年m月'),
            'prevMonth'    => $prevMonth,
            'nextMonth'    => $nextMonth,
        ]);
    }

    //勤怠詳細画面を表示するための関数(機能)
    public function show(int $id): View
    {
        //ログインユーザー情報から勤怠登録データと一緒に休憩データを持ってきて変数(箱)にしまう
        $record = AttendanceRecord::with('breakLogs')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        //勤怠詳細画面を表示する
        return view('attendance_detail', compact('record'));
    }

    // 修正ボタン押下後の画面切り替え機能を使うための関数(機能)
    public function update(UpdateAttendanceRequest $request, int $id): RedirectResponse
    {
        // 備考欄の未入力チェック、出勤・退勤時間の前後関係の入力チェックを行う
        $request->validate([
            'remarks'   => ['required'],
            'clock_in'  => ['required'],
            'clock_out' => ['required', 'after:clock_in'],
        ], [
            'remarks.required' => '備考を記入してください',
            'clock_out.after'  => '出勤時間が不適切な値です',
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

        // 勤怠登録データからユーザーの修正する情報を見つける
        $record = AttendanceRecord::findOrFail($id);

        // 元の勤怠は書き換えず、勤怠修正申請データを承認待ちとして作成する
        StampCorrectionRequest::create([
            'user_id'              => auth()->id(),
            'attendance_record_id' => $record->id,
            'status'               => 'pending',
            'reason'               => $request->input('remarks'),
        ]);

        // 申請一覧画面へ遷移する
        return redirect('/stamp_correction_request/list');
    }
}
