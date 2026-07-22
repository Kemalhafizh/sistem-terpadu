<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QueueDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('failed_jobs')->count() === 0) {
            DB::table('failed_jobs')->insert([
                [
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'connection' => 'database',
                    'queue' => 'high',
                    'payload' => json_encode(['job' => 'App\\Jobs\\SendHighPriorityNotificationJob']),
                    'exception' => 'RuntimeException: Gateway WhatsApp Timeout 504 Gateway Time-out at App\\Jobs\\SendHighPriorityNotificationJob:24',
                    'failed_at' => now()->subMinutes(15),
                ],
                [
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'connection' => 'database',
                    'queue' => 'low',
                    'payload' => json_encode(['job' => 'App\\Jobs\\GeneratePdfReportJob']),
                    'exception' => 'ErrorException: Out of Memory 128MB limit exceeded at App\\Jobs\\GeneratePdfReportJob:30',
                    'failed_at' => now()->subMinutes(5),
                ],
            ]);
        }
    }
}
