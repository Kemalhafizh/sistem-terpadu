<?php

namespace App\Http\Controllers\Api\v1\Concerns;

use App\Services\Academic\ClassSessionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * FASE 117: every class-session endpoint answers in the { success, message,
 * data } envelope Android's ApiEnvelope<T> deserialises — validation errors
 * and business-rule rejections included (plus "errors" for field messages).
 */
trait RespondsForClassSessions
{
    /**
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    protected function validateEnvelope(Request $request, array $rules, array $messages = []): array
    {
        return Validator::make($request->all(), $rules, $messages)->validate();
    }

    protected function validationFailed(ValidationException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $e->validator->errors()->first() ?: 'Validasi data gagal',
            'data' => null,
            'errors' => $e->errors(),
        ], 422);
    }

    protected function rejected(ClassSessionException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'data' => $e->data,
        ], $e->status);
    }

    /**
     * Run a handler, turning validation and business-rule failures into envelopes.
     */
    protected function handle(callable $handler): JsonResponse
    {
        try {
            return $handler();
        } catch (ValidationException $e) {
            return $this->validationFailed($e);
        } catch (ClassSessionException $e) {
            return $this->rejected($e);
        }
    }
}
