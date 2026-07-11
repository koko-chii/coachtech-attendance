<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
// 勤怠データの操作をするモデル
use App\Models\AttendanceRecord;
// APIで返すJSONに変換する
use App\Http\Resources\AttendanceRecordResource;
// 勤怠一覧を取得するためのバリデーション
use App\Http\Requests\Api\V1\IndexAttendanceRecordRequest;
// 勤怠データの新規作成をするためのバリデーション
use App\Http\Requests\Api\V1\StoreAttendanceRecordRequest;
// 勤怠データを更新するためのバリデーション
use App\Http\Requests\Api\V1\UpdateAttendanceRecordRequest;
// JSONレスポンスを返すための読み込み
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
// HTTPレスポンスを返すための読み込み
use Illuminate\Http\Response;
// API用JSON形式変換
use Illuminate\Http\JsonResponse;
// 認証機能を使用するための読み込み
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

// API用に勤怠データを操作するためのコントローラークラス
class AttendanceRecordController extends Controller
{
    // 操作権限をチェック
    use AuthorizesRequests;

    /**
     * 勤怠一覧をJSONレスポンスで返すための処理
     *
     * @param IndexAttendanceRecordRequest $request 検索条件やページ数が含まれるリクエスト箱
     * @return AnonymousResourceCollection API用の勤怠データコレクション（複数件）
     */
    public function index(IndexAttendanceRecordRequest $request): AnonymousResourceCollection
    {
        $perPage = (int) $request->input('per_page', 20);

        // 勤怠データの取得
        $records = AttendanceRecord::with(['user', 'breaks'])
            // 指定したスタッフIDの勤怠データを探す
            ->when($request->filled('user_id'), function ($query) use ($request) {
                $query->where('user_id', $request->input('user_id'));
            })
            // 指定した日付けの勤怠データ取得
            ->when($request->filled('date'), function ($query) use ($request) {
                $query->where('date', $request->input('date'));
            })
            // 指定した月の勤怠データ取得
            ->when($request->filled('month'), function ($query) use ($request) {
                $query->where('date', 'like', $request->input('month') . '%');
            })
            ->latest('date')
            // 指定した1ページの勤怠データを取得
            ->paginate($perPage);

        // 取得した勤怠データをJSONに変換して正しく表示して返す
        return AttendanceRecordResource::collection($records);
    }

    /**
     * スマホ等で送られた勤怠データを登録し、API用にJSON形式に返す処理
     *
     * @param StoreAttendanceRecordRequest $request 新規登録データが含まれるリクエスト箱
     * @return JsonResponse ステータスコード201を含むAPI用JSONレスポンス
     */
    public function store(StoreAttendanceRecordRequest $request): JsonResponse
    {
        // API用のバリデーションチェック済みの勤怠データを作成
        $attendanceRecord = $request->user()->attendanceRecords()->create($request->validated());

        $attendanceRecord->load(['user', 'breaks']);

        // 勤怠データをAPI用のJSON形式で返す
        return (new AttendanceRecordResource($attendanceRecord))
            ->response()
            ->setStatusCode(201);
    }

    // API用の勤怠詳細画面を返すための処理
    public function show(AttendanceRecord $attendanceRecord): AttendanceRecordResource
    {
        // 勤怠データに関するスタッフデータ・休憩データ・修正申請データを読み込む
        $attendanceRecord->load(['user', 'breaks', 'applications']);

        // 勤怠詳細データをAPI用のJSON形式で返す
        return new AttendanceRecordResource($attendanceRecord);
    }

    // API用の勤怠データを更新処理
    public function update(UpdateAttendanceRecordRequest $request, AttendanceRecord $attendanceRecord): AttendanceRecordResource
    {
        // 更新権限をチェック
        $this->authorize('update', $attendanceRecord);

        // バリデーションチェック済みの勤怠データを更新
        $attendanceRecord->update($request->validated());

        $attendanceRecord->load(['user', 'breaks']);

        // 更新した勤怠データをAPI用のJSON形式で返す
        return new AttendanceRecordResource($attendanceRecord);
    }

    // 勤怠データを削除する処理
    public function destroy(AttendanceRecord $attendanceRecord): Response
    {
        // 勤怠データを削除する権限チェック
        $this->authorize('delete', $attendanceRecord);

        // 勤怠データの削除
        $attendanceRecord->delete();

        // 削除し返すデータはないレスポンスを返す
        return response()->noContent();
    }
}
