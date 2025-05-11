<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;

final class PingController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): JsonResponse
    {
        return response()->success([
            'timestamp' => now()->timestamp,
            'version' => '1.0.0',
            'health' => 'ok',
        ], 'API is up and running');
    }
}
