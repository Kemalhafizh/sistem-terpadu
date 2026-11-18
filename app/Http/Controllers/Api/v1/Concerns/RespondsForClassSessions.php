<?php

namespace App\Http\Controllers\Api\v1\Concerns;

use App\Services\Academic\ClassSessionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * every class-session endpoint answers in the { success, message,
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

    /**
     * The `date` query of the per-lesson attendance endpoints: Y-m-d, today
     * by default, never in the future (nothing has been recorded there).
     *
     * @throws ValidationException
     */
    protected function attendanceDate(Request $request): string
    {
        $today = now()->toDateString();
        $data = Validator::make($request->query(), [
            'date' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:'.$today],
        ], [
            'date.before_or_equal' => __('Tanggal presensi tidak boleh di masa depan.'),
        ])->validate();

        return $data['date'] ?? $today;
    }

    protected function validationFailed(ValidationException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $e->validator->errors()->first() ?: __('Validasi data gagal'),
            'data' => null,
            'errors' => $e->errors(),
        ], 422);
    }

    protected function rejected(ClassSessionException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'error_code' => $e->errorCode,
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
