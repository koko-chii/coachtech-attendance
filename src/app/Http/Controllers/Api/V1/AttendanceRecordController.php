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

    public function store(StoreAttendanceRecordRequest $request): AttendanceRecordResource
    {
        $record = AttendanceRecord::create($request->validated());

        return new AttendanceRecordResource($record);
    }

    public function show(AttendanceRecord $attendanceRecord): AttendanceRecordResource
    {
        $attendanceRecord->load(['user', 'breakLogs', 'stampCorrectionRequests']);

        return new AttendanceRecordResource($attendanceRecord);
    }

    public function update(UpdateAttendanceRecordRequest $request, AttendanceRecord $attendanceRecord): AttendanceRecordResource
    {
        $this->authorize('update', $attendanceRecord);

        $attendanceRecord->update($request->validated());

        return new AttendanceRecordResource($attendanceRecord);
    }

    public function destroy(AttendanceRecord $attendanceRecord): Response
    {
        $this->authorize('delete', $attendanceRecord);

        $attendanceRecord->delete();

        return response()->noContent();
    }
}
