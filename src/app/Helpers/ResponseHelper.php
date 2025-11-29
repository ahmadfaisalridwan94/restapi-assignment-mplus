<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ResponseHelper
{
    public static function jsonResponse($status = true, $code = "0000", $message, $data = [], $statusCode = 200): JsonResponse
    {
        return response()->json([
            'status' => $status,
            'code' => $code,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }
}
