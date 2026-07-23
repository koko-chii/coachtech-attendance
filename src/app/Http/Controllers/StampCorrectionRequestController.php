<?php

namespace App\Http\Controllers;

use App\Models\StampCorrectionRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Http\Controllers\Admin\AdminAttendanceController;
use Illuminate\Http\Request;

class StampCorrectionRequestController extends Controller
{
    /**
     * 勤怠申請一覧画面を表示する（管理者の場合は管理者用一覧処理に委譲する）
     *
     * @param Request $request セッション情報を含むリクエスト
     * @return View 勤怠申請一覧画面のビュー
     */
    public function index(Request $request): View
    {
        // ログイン中のユーザー情報を取得
        $user = Auth::user();

        // 管理者ログインの場合、管理者用の申請一覧画面を表示
        if ($user && $user->admin_status && $request->session()->get('login_entrance') !== 'staff') {
            return app(AdminAttendanceController::class)->showRequestList();
        }
        // ログインユーザーIDを取得
        $userId = Auth::id();

        // ログインユーザーの修正申請データーを勤怠登録データと一緒に取得
        $allRequests = StampCorrectionRequest::with(['attendanceRecord'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'asc')
            ->get();

        // 承認待ち申請を抽出
        $pendingRequests = $allRequests->filter(
            function (StampCorrectionRequest $request): bool {
                return $request->status === 'pending';
            }
        );

        // 承認済み申請を抽出
        $approvedRequests = $allRequests->filter(
            function (StampCorrectionRequest $request): bool {
                return $request->status === 'approved';
            }
        );

        return view('stamp_correction_request.list', compact('pendingRequests', 'approvedRequests'));
    }
}
