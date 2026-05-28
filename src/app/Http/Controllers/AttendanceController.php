<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRecord;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * 🖥️ 1. 打刻画面を表示する（今日の状態をチェックする）
     */
    public function index()
    {
        $userId = Auth::id();
        $today = Carbon::today()->format('Y-m-d');

        // 💡 データベースから「ログイン中のユーザーの今日の出勤記録」を1件探します
        $attendance = AttendanceRecord::where('user_id', $userId)
            ->where('date', $today)
            ->first();

        // 画面（attendance.blade.php）に、見つかったデータを渡します
        return view('attendance', compact('attendance'));
    }

    /**
     * 🕒 2. 出勤ボタンが押されたときの保存処理
     */
    public function clockIn(Request $request)
    {
        $userId = Auth::id();
        $today = Carbon::today()->format('Y-m-d');
        $now = Carbon::now()->format('H:i:s');

        AttendanceRecord::create([
            'user_id' => $userId,
            'date' => $today,
            'clock_in' => $now,
        ]);

        return redirect('/attendance');
    }
}
