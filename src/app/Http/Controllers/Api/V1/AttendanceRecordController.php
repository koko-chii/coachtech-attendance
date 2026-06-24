<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Http\Resources\AttendanceRecordResource;
use App\Http\Requests\Api\V1\IndexAttendanceRecordRequest;
use App\Http\Requests\Api\V1\StoreAttendanceRecordRequest;
use App\Http\Requests\Api\V1\UpdateAttendanceRecordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AttendanceRecordController extends Controller
{
    use AuthorizesRequests;

    public function index(IndexAttendanceRecordRequest $request): JsonResponse
    {
        $query = AttendanceRecord::query();

        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->has('date')) {
            $query->where('date', $request->input('date'));
        }

        if ($request->has('month')) {
            $query->where('date', 'like', $request->input('month') . '%');
        }

        $perPage = $request->input('per_page', 20);
        $perPage = min(max((int)$perPage, 1), 100);

        $records = $query->paginate($perPage);

        return response()->json([
            'data' => AttendanceRecordResource::collection($records->items()),
            'meta' => [
                'current_page' => $records->currentPage(),
                'last_page' => $records->lastPage(),
                'per_page' => $records->perPage(),
                'total' => $records->total(),
            ]
        ], 200);
    }

    public function store(StoreAttendanceRecordRequest $request): JsonResponse
    {
        $record = AttendanceRecord::create($request->validated());

        return response()->json(new AttendanceRecordResource($record), 201);
    }

    public function show(int $id): JsonResponse
    {
        $record = AttendanceRecord::with(['user', 'breakLogs', 'stampCorrectionRequests'])->find($id);

        if (!$record) {
            return response()->json([
                'error' => '勤怠情報が見つかりませんでした。'
            ], 404);
        }

        return response()->json(new AttendanceRecordResource($record), 200);
    }

    public function update(UpdateAttendanceRecordRequest $request, int $id): JsonResponse
    {
        $record = AttendanceRecord::find($id);

        if (!$record) {
            return response()->json([
                'error' => '勤怠情報が見つかりませんでした。'
            ], 404);
        }

        $this->authorize('update', $record);

        $record->update($request->validated());

        return response()->json(new AttendanceRecordResource($record), 200);
    }

    public function destroy(int $id): Response
    {
        $record = AttendanceRecord::find($id);

        if (!$record) {
            return response([
                'error' => '勤怠情報が見つかりませんでした。'
            ], 404);
        }

        $this->authorize('delete', $record);

        $record->delete();

        return response()->noContent();
    }
}
