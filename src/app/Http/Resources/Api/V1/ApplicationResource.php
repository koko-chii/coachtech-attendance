<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

// APIレスポンス用のJSONデータに変換する機能をを継承した共通クラス
class ApplicationResource extends JsonResource
{
    // APIレスポンスの内容を配列で返す処理
    public function toArray(Request $request): array
    {
        // IDをJSONデータとして返す
        return [
            'id' => $this->id,
        ];
    }
}
