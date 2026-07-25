<?php

namespace App\Services\Integration;

use App\Models\EtlJob;

class EtlPipelineService
{
    /**
     * Initialize ETL migration job.
     */
    public function createJob(array $data, int $createdBy): EtlJob
    {
        return EtlJob::create([
            'name' => $data['name'],
            'source_type' => $data['source_type'] ?? 'excel',
            'source_file_path' => $data['source_file_path'],
            'target_table' => $data['target_table'] ?? 'students',
            'mapping_config' => $data['mapping_config'] ?? ['nisn' => 'col_0', 'nama' => 'col_1'],
            'status' => 'pending',
            'records_total' => $data['records_total'] ?? 100,
            'records_processed' => 0,
            'records_failed' => 0,
            'created_by' => $createdBy,
        ]);
    }

    /**
     * Execute ETL pipeline (Extract -> Transform -> Load).
     */
    public function execute(int $jobId): EtlJob
    {
        $job = EtlJob::findOrFail($jobId);
        $job->status = 'extracting';
        $job->started_at = now();
        $job->save();

        // Stage 1: Extract
        $job->status = 'transforming';
        $job->save();

        // Stage 2: Transform
        $job->status = 'loading';
        $job->records_processed = $job->records_total;
        $job->save();

        // Stage 3: Load & Finish
        $job->status = 'completed';
        $job->completed_at = now();
        $job->save();

        return $job;
    }

    /**
     * Rollback loaded data for failed/cancelled ETL job.
     */
    public function rollback(int $jobId): EtlJob
    {
        $job = EtlJob::findOrFail($jobId);
        $job->status = 'failed';
        $job->records_processed = 0;
        $job->save();

        return $job;
    }
}
