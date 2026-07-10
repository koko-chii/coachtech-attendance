<?php

namespace App\Http\Controllers;

use App\Models\StampCorrectionRequest;
use Illuminate\Support\Facades\Auth;
// Laravel標準のView機能を使うための読み込み
use Illuminate\View\View;
// 管理者コントローラーの読み込み
use App\Http\Controllers\Admin\AdminAttendanceController;

// コントローラー機能を継承した勤怠修正申請機能を作成するためのクラス
class StampCorrectionRequestController extends Controller
{
    // 勤怠申請一覧画面を表示するための関数(機能)
    public function index(): View
    {
        // ログイン中のユーザー情報を取得
        $user = Auth::user();

        // もし管理者（admin_statusがtrue）なら、管理者用の画面処理へ丸投げする
        if ($user && $user->admin_status) {
            return app(AdminAttendanceController::class)->showRequestList();
        }
        // ログイン中のユーザーIDを取得
        $userId = Auth::id();

        // ログインユーザーの修正申請データーを勤怠登録データと一緒に取得
        $allRequests = StampCorrectionRequest::with(['attendanceRecord'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'asc')
            ->get();

        // 取得したデータの中から承認待ちデーターを抽出
        $pendingRequests = $allRequests->filter(
            function (StampCorrectionRequest $request): bool {
                return $request->status === 'pending';
            }
        );

        // 取得したデータの中から承認済みデーターを抽出
        $approvedRequests = $allRequests->filter(
            function (StampCorrectionRequest $request): bool {
                return $request->status === 'approved';
            }
        );

        // 勤怠申請一覧画面で承認待ちと承認済みを表示する
        return view('stamp_correction_request.list', compact('pendingRequests', 'approvedRequests'));
    }
}
