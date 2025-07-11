<?php

namespace App\Services\Performance;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueryOptimizer
{
    /**
     * Log slow queries above a threshold.
     *
     * @param float $threshold Threshold in seconds
     * @return void
     */
    public function monitorSlowQueries(float $threshold = 1.0): void
    {
        DB::listen(function ($query) use ($threshold) {
            if ($query->time > ($threshold * 1000)) {
                Log::warning('Slow query detected:', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time . 'ms',
                ]);
            }
        });
    }

    /**
     * Optimize common queries by adding indexes.
     *
     * @return array Queries that need optimization
     */
    public function analyzeQueryPerformance(): array
    {
        $issues = [];
        
        // Check for queries without indexes
        $slowQueries = DB::select("
            SELECT 
                table_name,
                index_name,
                COUNT(*) as usage_count
            FROM 
                information_schema.statistics
            WHERE 
                table_schema = DATABASE()
            GROUP BY 
                table_name, index_name
            HAVING 
                usage_count < 10
        ");

        foreach ($slowQueries as $query) {
            $issues[] = [
                'table' => $query->table_name,
                'index' => $query->index_name,
                'usage' => $query->usage_count,
                'suggestion' => 'Consider adding index for frequently queried columns'
            ];
        }

        return $issues;
    }
}
