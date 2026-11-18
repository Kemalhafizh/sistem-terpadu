<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

trait ApiResponseTrait
{
    /**
     * Build a success response.
     */
    protected function successResponse($data, $message = null, $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Build an error response.
     */
    protected function errorResponse($message, $code): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
        ], $code);
    }

    /**
     * same shape as successResponse(), plus ETag (a hash of the
     * actual payload — changes iff the data changes) and, when the caller
     * has a real "as of" timestamp for the data, Last-Modified. Only for
     * read endpoints where a client needs a conditional-request validator
     * (e.g. offline sync polling); mutating endpoints get freshness from
     * IdempotencyMiddleware instead.
     */
    protected function successResponseWithValidators($data, ?Carbon $lastModified = null, $message = null, $code = 200): JsonResponse
    {
        $response = $this->successResponse($data, $message, $code);
        $response->headers->set('ETag', '"'.sha1(json_encode($data)).'"');
        if ($lastModified) {
            $response->headers->set('Last-Modified', $lastModified->toRfc7231String());
        }

        return $response;
    }
}
