<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AttendanceController;

/*

|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 💡 ログインせずに「http://localhost/」にアクセスした場合は自動でログイン画面へ
Route::get('/', function () {
    return redirect('/login');
});

// ==========================================
// 🧪 メール認証の学習テスト用（強制パスルート）
// ==========================================
Route::post('/email/bypass', function () {
    $user = Auth::user();

    if ($user && !$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
    }

    return redirect()->route('attendance.index');
})->middleware('auth')->name('email.bypass');


// ==========================================
// 💼 勤怠管理システム ルート一覧（要メール認証ガード）
// ==========================================

// 1. ダッシュボード
Route::get('/dashboard', function () {
    return view('layouts.authenticated');
})->middleware(['auth', 'verified'])->name('dashboard');

// 2. 打刻画面を表示するルート
Route::get('/attendance', [AttendanceController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('attendance.index');

// 3. 出勤ボタンを押したときの保存処理
Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])
    ->middleware(['auth', 'verified'])
    ->name('attendance.clock-in');

// 4. 退勤ボタンを押したときの保存処理
Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])
    ->middleware(['auth', 'verified'])
    ->name('attendance.clock-out');

// 5. 💡 【追記】休憩入・休憩戻ボタンを押したときの保存処理
Route::post('/attendance/break', [AttendanceController::class, 'break'])
    ->middleware(['auth', 'verified'])
    ->name('attendance.break');
