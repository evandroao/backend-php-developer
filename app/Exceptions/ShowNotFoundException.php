<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ShowNotFoundException extends Exception
{
    public function __construct(string $showName)
    {
        parent::__construct("Show not found in TVMaze: {$showName}");
    }

    public function render(Request $request): JsonResponse
    {
        if ($request->is('api/*')) {
            return response()->json([
                'message' => $this->getMessage(),
                'path' => '/' . $request->path(),
                'status' => 404,
                'error' => 'Not Found',
                'timestamp' => now()->toIso8601String(),
            ], 404);
        }
    }
}
