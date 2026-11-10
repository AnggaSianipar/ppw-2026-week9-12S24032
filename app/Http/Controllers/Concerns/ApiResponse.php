<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    // Helper opsional untuk controller tambahan agar format sukses/error seragam.
    protected function success(
        mixed $data = null,
        string $message = 'Request berhasil.',
        int $status = 200,
        array $meta = []
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if ($meta !== []) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $status);
    }

    protected function problem(
        string $title,
        string $detail,
        int $status,
        string $instance
    ): JsonResponse {
        return response()->json([
            'type' => 'https://httpstatuses.com/' . $status,
            'title' => $title,
            'status' => $status,
            'detail' => $detail,
            'instance' => $instance,
        ], $status, [
            'Content-Type' => 'application/problem+json',
        ]);
    }
}