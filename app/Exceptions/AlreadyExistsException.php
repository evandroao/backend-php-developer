<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class AlreadyExistsException extends Exception
{
    public function __construct(string $entity, string $identifier)
    {
        parent::__construct("{$entity} already exists: {$identifier}");
    }
    public function render(Request $request): JsonResponse {
      if ($request->is('api/*')) {
        return response()->json([
          'message' => $this->getMessage(),
          'path' => '/' . $request->path(),
          'status' => 409,
          'error' => 'Conflict',
          'timestamp' => now()->toIso8601String(),
        ], 409);
      }
    }
}
