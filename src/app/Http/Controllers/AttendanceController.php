<?php

namespace App\Http\Controllers;

//リクエスト機能を使うための呼び出し
use Illuminate\Http\Request;
//認証機能を使うための呼び出し
use Illuminate\Support\Facades\Auth;
//データーベースの情報を使うための呼び出し
use Illuminate\Support\Facades\DB;
//現在の時刻の取得や、勤務時間の計算機能を使うための呼び出し
use Carbon\Carbon;
//勤務時間の打刻機能を使うための呼び出し
use App\Models\AttendanceRecord;

//勤怠管理コントローラーを作成するためのクラス(設置)
class AttendanceController extends Controller
{
    //勤怠管理画面を表示するための関数(機能)
    public function index()
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
        // データーベースの休憩中データーを箱にしまう。
        // 勤怠管理データーから探し出し休憩から戻ってないか調べる。　
        if ($attendance && !$attendance->clock_out) {
            $is_breaking = DB::table('breaks')
                ->where('attendance_record_id', $attendance->id)
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
    public function clockIn()
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

        //ダイレクトに返す
        return redirect()->back();
    }

    //退勤ボタンが押されたときの関数(機能)
    public function clockOut()
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

        //ダイレクトに返す
        return redirect()->back();
    }

    //休憩ボタンが押されたときの関数(機能)
    public function break()
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
        $activeBreak = DB::table('breaks')
            ->where('attendance_record_id', $attendance->id)
            ->whereNull('break_out')
            ->first();

        //もしたくさんの休憩情報のなかに休憩中を見つけたらactiveBreak変数(箱)へしまう
        //休憩戻を押したら、now変数(箱)のデータを現在の時刻形式で更新する
        if ($activeBreak) {
            DB::table('breaks')
                ->where('id', $activeBreak->id)
                ->update([
                    'break_out'  => $now->toTimeString(),
                    'updated_at' => $now,
                ]);
        }
        //休憩中情報が無かったら、新しく休憩中テーブル
        //(勤怠管理情報、休憩入時刻形式情報、新規休憩入り情報、更新情報)を登録する
        else {
            DB::table('breaks')->insert([
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
    public function showList(Request $request)
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

        //勤怠登録データーからこのユーザー情報を探す
        //ユーザーが指定した年月データーを取ってきて
        // 日付をキー(見出し)にして箱(attendances)に整理する
        $attendances = AttendanceRecord::where('user_id', $user->id)
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
}
