<?php

//laravelのルーティング機能を読み込み
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AttendanceController;

// ログインせずにアプリのトップ（/）にアクセスした場合は、自動的にログイン画面へ転送
Route::get('/', function () {
    return redirect('/login');
});

// Mailpitの画面へ簡単に移動するためのルート(開発が終わったら削除してOK)
Route::get('/email/go-to-mailpit', function () {
    return redirect('http://localhost:8025');
});

// メール認証機能確認用の強制パスルート
Route::post('/email/bypass', function () {
    //ログインしているユーザー情報を取得
    $user = Auth::user();

    //メール認証機能確認用ユーザーはメール認証しなくても認証済みにする
    if ($user && !$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
    }

    //勤怠登録画面へ遷移する
    return redirect()->route('attendance.index');
})->middleware('auth')->name('email.bypass');

// 勤怠登録画面(打刻画面)を表示するルート
//ログイン認証とメール認証が済みのユーザーがアクセス可能
//打刻画面呼び出し用ルート
Route::get('/attendance', [AttendanceController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('attendance.index');

// 出勤ボタンを押したときの保存処理
Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])
    ->middleware(['auth', 'verified'])
    ->name('attendance.clock-in');

// 退勤ボタンを押したときの保存処理
Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])
    ->middleware(['auth', 'verified'])
    ->name('attendance.clock-out');

// 休憩入・休憩戻ボタンを押したときの保存処理
Route::post('/attendance/break', [AttendanceController::class, 'break'])
    ->middleware(['auth', 'verified'])
    ->name('attendance.break');

//勤怠一覧画面を表示するルート
Route::get('/attendance/list', [AttendanceController::class, 'showList'])
    ->middleware(['auth', 'verified'])
    ->name('attendance.list');

//勤怠一覧詳細画面を表示するルート
Route::get('/attendance/detail/{id}', [AttendanceController::class, 'showDetail'])
    ->middleware(['auth', 'verified'])
    ->name('attendance.detail');

