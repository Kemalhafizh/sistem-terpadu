<?php

namespace App\Services\Student;

use App\Models\StudentRequest;

class StudentSelfService
{
    public function submitRequest(int $studentId, string $requestType, array $details = []): StudentRequest
    {
        return StudentRequest::create([
            'student_id' => $studentId,
            'request_type' => $requestType,
            'status' => 'pending',
            'details' => $details,
        ]);
    }

    public function processRequest(int $requestId, int $processorId, string $status, ?string $adminNotes = null): StudentRequest
    {
        $request = StudentRequest::findOrFail($requestId);
        $request->update([
            'status' => $status,
            'processed_by' => $processorId,
            'admin_notes' => $adminNotes,
            'resolved_at' => in_array($status, ['completed', 'rejected']) ? now() : null,
        ]);

        return $request;
    }
}
