<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Http\Resources\AttendanceRecordResource;
use App\Http\Requests\Api\V1\IndexAttendanceRecordRequest;
use App\Http\Requests\Api\V1\StoreAttendanceRecordRequest;
use App\Http\Requests\Api\V1\UpdateAttendanceRecordRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AttendanceRecordController extends Controller
{
    // 操作権限をチェック
    use AuthorizesRequests;

    /**
     * 勤怠一覧を取得する
     *
     * @param IndexAttendanceRecordRequest $request リクエスト情報
     * @return AnonymousResourceCollection 勤怠一覧のJSONレスポンス
     */
    public function index(IndexAttendanceRecordRequest $request): AnonymousResourceCollection
    {
        // 1ページ20件
        $perPage = (int) $request->input('per_page', 20);

        // 勤怠データの取得
        $records = AttendanceRecord::with(['user', 'breaks'])
            // スタッフDで絞り込み
            ->when($request->filled('user_id'), function ($query) use ($request) {
                $query->where('user_id', $request->input('user_id'));
            })
            // 日付けで絞り込み
            ->when($request->filled('date'), function ($query) use ($request) {
                $query->where('date', $request->input('date'));
            })
            // 月で絞り込み
            ->when($request->filled('month'), function ($query) use ($request) {
                $query->where('date', 'like', $request->input('month') . '%');
            })
            // 日付けの新しい順に並び替え
            ->latest('date')
            // 指定件数ごとにページ分けして取得
            ->paginate($perPage);

        return AttendanceRecordResource::collection($records);
    }

    /**
     * 勤怠データを登録する
     *
     * @param StoreAttendanceRecordRequest $request リクエスト情報
     * @return JsonResponse 登録した勤怠データのJSONレスポンス
     */
    public function store(StoreAttendanceRecordRequest $request): JsonResponse
    {
        // 勤怠データを登録
        $attendanceRecord = $request->user()->attendanceRecords()->create($request->validated());

        // 関連データの読み込み
        $attendanceRecord->load(['user', 'breaks']);

        return (new AttendanceRecordResource($attendanceRecord))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * 勤怠詳細を取得する
     *
     * @param AttendanceRecord $attendanceRecord 勤怠データ
     * @return AttendanceRecordResource 勤怠詳細のJSONレスポンス
     */
    public function show(AttendanceRecord $attendanceRecord): AttendanceRecordResource
    {
        // 勤怠データを読み込む
        $attendanceRecord->load(['user', 'breaks', 'applications']);

        return new AttendanceRecordResource($attendanceRecord);
    }

    /**
     * 勤怠データを更新する
     *
     * @param UpdateAttendanceRecordRequest $request リクエスト情報
     * @param AttendanceRecord $attendanceRecord 更新対象の勤怠データ
     * @return AttendanceRecordResource 更新後の勤怠データのJSONレスポンス
     */
    public function update(UpdateAttendanceRecordRequest $request, AttendanceRecord $attendanceRecord): AttendanceRecordResource
    {
        // 更新権限を確認
        $this->authorize('update', $attendanceRecord);

        // 勤怠データを更新
        $attendanceRecord->update($request->validated());

        // 関連データの読み込み
        $attendanceRecord->load(['user', 'breaks']);

        return new AttendanceRecordResource($attendanceRecord);
    }

    /**
     * 勤怠データを削除する
     *
     * @param AttendanceRecord $attendanceRecord 削除対象の勤怠データ
     * @return Response 削除成功レスポンス
     */
    public function destroy(AttendanceRecord $attendanceRecord): Response
    {
        // 削除権限を確認
        $this->authorize('delete', $attendanceRecord);

        // 勤怠データの削除
        $attendanceRecord->delete();

        return response()->noContent();
    }
}
