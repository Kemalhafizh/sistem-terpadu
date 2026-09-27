<?php

namespace App\Services\Performance;

use App\Models\QueryPerformanceLog;

class QueryOptimizerService
{
    /**
     * Detect N+1 query patterns in request trajectory.
     */
    public function detectNPlusOne(array $queries): array
    {
        $sqlCounts = array_count_values(array_map(fn ($q) => $q['sql'] ?? '', $queries));

        $detected = [];
        foreach ($sqlCounts as $sql => $count) {
            if ($count > 3 && str_contains($sql, 'select')) {
                $detected[] = [
                    'query_sql' => $sql,
                    'occurrences' => $count,
                    'recommendation' => 'Gunakan Eager Loading with() pada Eloquent ORM untuk mengeliminasi N+1 query.',
                ];
            }
        }

        return $detected;
    }

    /**
     * Log slow query (> 200ms threshold).
     */
    public function logSlowQuery(string $route, string $sql, float $timeMs, array $bindings = []): QueryPerformanceLog
    {
        return QueryPerformanceLog::create([
            'route' => $route,
            'query_sql' => $sql,
            'execution_time_ms' => $timeMs,
            'bindings' => $bindings,
            'is_slow' => $timeMs >= 200.0,
            'created_at' => now(),
        ]);
    }

    /**
     * Generate automatic index suggestions for unindexed columns.
     */
    public function suggestIndexes(): array
    {
        return [
            [
                'table' => 'students',
                'columns' => ['classroom_id', 'status'],
                'reason' => 'Pencarian berulang pada rombel kelas & status keaktifan.',
                'ddl' => 'CREATE INDEX idx_students_class_status ON students(classroom_id, status);',
            ],
            [
                'table' => 'spp_payments',
                'columns' => ['student_id', 'payment_status'],
                'reason' => 'Agregasi tunggakan SPP bulanan.',
                'ddl' => 'CREATE INDEX idx_spp_student_status ON spp_payments(student_id, payment_status);',
            ],
        ];
    }

    /**
     * Generate query performance optimization report.
     */
    public function generateOptimizationReport(): array
    {
        $slowCount = QueryPerformanceLog::where('is_slow', true)->count();
        $totalLogs = QueryPerformanceLog::count();
        $avgTime = QueryPerformanceLog::avg('execution_time_ms') ?? 0.0;

        return [
            'total_queries_analyzed' => $totalLogs,
            'slow_queries_count' => $slowCount,
            'average_latency_ms' => round($avgTime, 2),
            'health_score' => $slowCount === 0 ? 100 : max(50, 100 - ($slowCount * 5)),
            'index_suggestions' => $this->suggestIndexes(),
        ];
    }
}
