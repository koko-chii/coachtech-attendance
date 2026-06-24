<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AttendanceRecordController;

Route::prefix('v1')->group(function () {
    Route::get('attendance-records', [AttendanceRecordController::class, 'index']);
    Route::get('attendance-records/{id}', [AttendanceRecordController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('attendance-records', [AttendanceRecordController::class, 'store']);
        Route::put('attendance-records/{id}', [AttendanceRecordController::class, 'update']);
        Route::delete('attendance-records/{id}', [AttendanceRecordController::class, 'destroy']);
    });
});
