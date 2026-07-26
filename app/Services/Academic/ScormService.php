<?php

namespace App\Services\Academic;

use App\Models\ScormPackage;
use App\Models\ScormTracking;
use App\Models\XapiStatement;

class ScormService
{
    public function uploadPackage(array $data, int $uploaderId): ScormPackage
    {
        return ScormPackage::create([
            'title' => $data['title'],
            'version' => $data['version'] ?? 'scorm12',
            'file_path' => $data['file_path'],
            'entry_point' => $data['entry_point'] ?? 'index.html',
            'uploaded_by' => $uploaderId,
            'status' => 'ready',
        ]);
    }

    public function recordTracking(ScormPackage $package, int $studentId, array $trackingData): ScormTracking
    {
        return ScormTracking::updateOrCreate(
            [
                'scorm_package_id' => $package->id,
                'student_id' => $studentId,
            ],
            [
                'lesson_status' => $trackingData['lesson_status'] ?? 'completed',
                'score_raw' => $trackingData['score_raw'] ?? 100,
                'score_max' => $trackingData['score_max'] ?? 100,
                'total_time_seconds' => $trackingData['total_time_seconds'] ?? 300,
                'last_accessed_at' => now(),
            ]
        );
    }

    public function recordXapiStatement(int $actorId, string $verb, string $objectType, string $objectId, ?array $result = null): XapiStatement
    {
        return XapiStatement::create([
            'actor_id' => $actorId,
            'verb' => $verb,
            'object_type' => $objectType,
            'object_id' => $objectId,
            'result' => $result,
            'timestamp_at' => now(),
            'stored_at' => now(),
        ]);
    }
}
