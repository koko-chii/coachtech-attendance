<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\AttendanceRecord;

class AttendanceController extends Controller
{
    /**
     * FN018・FN019: 勤怠登録画面を表示する
     */
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();

        // 今日の出勤レコードを取得
        $attendance = AttendanceRecord::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        // 現在「休憩中」かどうかを判定するフラグ
        $is_breaking = false;

        if ($attendance && !$attendance->clock_out) {
            // 💡 確実に「breaks」テーブル内で break_out（休憩終了）が空のレコードがあるかダイレクトにチェック
            $is_breaking = DB::table('breaks')
                ->where('attendance_record_id', $attendance->id)
                ->whereNull('break_out')
                ->exists();
        }

        return view('attendance', [
            'attendance'  => $attendance,
            'is_breaking' => $is_breaking,
            'today'       => $today->format('Y年n月j日'),
        ]);
    }

    /**
     * FN020: 出勤ボタンを押したときの保存処理
     */
    public function clockIn()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $exists = AttendanceRecord::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->exists();

        if (!$exists) {
            AttendanceRecord::create([
                'user_id'  => $user->id,
                'date'     => $today->toDateString(),
                'clock_in' => Carbon::now()->toTimeString(),
            ]);
        }

        return redirect()->back();
    }

    /**
     * FN022: 退勤ボタンを押したときの保存処理
     */
    public function clockOut()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = AttendanceRecord::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($attendance && !$attendance->clock_out) {
            $attendance->update([
                'clock_out' => Carbon::now()->toTimeString(),
            ]);
        }

        return redirect()->back();
    }

    /**
     * FN021: 休憩入 / 休憩戻 ボタンを押したときの保存処理
     */
    public function break()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $now = Carbon::now();

        // 今日の出勤レコードを確実に取得
        $attendance = AttendanceRecord::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        // 出勤していない、または既に退勤している場合は何もしない
        if (!$attendance || $attendance->clock_out) {
            return redirect()->back();
        }

        // 💡 進行中の休憩（break_outが空のもの）があるかチェック
        $activeBreak = DB::table('breaks')
            ->where('attendance_record_id', $attendance->id)
            ->whereNull('break_out')
            ->first();

        if ($activeBreak) {
            // ⭕️ 【休憩戻】の処理（進行中の休憩を終了させる）
            DB::table('breaks')
                ->where('id', $activeBreak->id)
                ->update([
                    'break_out'  => $now->toTimeString(),
                    'updated_at' => $now,
                ]);
        } else {
            // ⭕️ 【休憩入】の処理（新規に休憩レコードを登録する）
            DB::table('breaks')->insert([
                'attendance_record_id' => $attendance->id,
                'break_in'             => $now->toTimeString(),
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);
        }

        return redirect()->back();
    }
}
