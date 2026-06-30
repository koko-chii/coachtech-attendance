<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\V1\UserResource;
use App\Http\Resources\Api\V1\AttendanceBreakResource;
use App\Http\Resources\Api\V1\ApplicationResource;

class AttendanceRecordResource extends JsonResource
{
    public static $wrap = 'data';

        public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'date' => $this->date,
            'clock_in' => $this->clock_in,
            'clock_out' => $this->clock_out,
            'user' => new UserResource($this->whenLoaded('user')),
            'breaks' => AttendanceBreakResource::collection($this->whenLoaded('breaks')),
            'stamp_correction_requests' => ApplicationResource::collection($this->whenLoaded('applications')),
        ];
    }

}
