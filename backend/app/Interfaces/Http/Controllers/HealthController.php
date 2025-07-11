<?php

namespace App\Interfaces\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Controller;

class HealthController extends Controller
{
    /**
     * @OA\Get(
     *     path="/health",
     *     tags={"Health"},
     *     summary="Health check endpoint",
     *     description="Verifica o status de saúde da aplicação e suas dependências",
     *     @OA\Response(
     *         response=200,
     *         description="Sistema saudável",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="healthy"),
     *             @OA\Property(property="timestamp", type="string", format="date-time"),
     *             @OA\Property(property="version", type="string", example="1.0.0"),
     *             @OA\Property(property="environment", type="string", example="production"),
     *             @OA\Property(
     *                 property="checks",
     *                 type="object",
     *                 @OA\Property(
     *                     property="database",
     *                     type="object",
     *                     @OA\Property(property="status", type="string", example="healthy"),
     *                     @OA\Property(property="message", type="string", example="Database connection successful")
     *                 ),
     *                 @OA\Property(
     *                     property="redis",
     *                     type="object",
     *                     @OA\Property(property="status", type="string", example="healthy"),
     *                     @OA\Property(property="message", type="string", example="Redis connection successful")
     *                 ),
     *                 @OA\Property(
     *                     property="storage",
     *                     type="object",
     *                     @OA\Property(property="status", type="string", example="healthy"),
     *                     @OA\Property(property="message", type="string", example="Storage write/read successful")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=503,
     *         description="Sistema com problemas",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="unhealthy"),
     *             @OA\Property(property="timestamp", type="string", format="date-time"),
     *             @OA\Property(property="version", type="string", example="1.0.0"),
     *             @OA\Property(property="environment", type="string", example="production"),
     *             @OA\Property(property="checks", type="object")
     *         )
     *     )
     * )
     *
     * Health check endpoint
     *
     * @return JsonResponse
     */
    public function check(): JsonResponse
    {
        $status = 'healthy';
        $checks = [];

        // Database check
        try {
            DB::connection()->getPdo();
            $checks['database'] = [
                'status' => 'healthy',
                'message' => 'Database connection successful'
            ];
        } catch (\Exception $e) {
            $status = 'unhealthy';
            $checks['database'] = [
                'status' => 'unhealthy',
                'message' => 'Database connection failed: ' . $e->getMessage()
            ];
        }

        // Redis check
        try {
            Redis::ping();
            $checks['redis'] = [
                'status' => 'healthy',
                'message' => 'Redis connection successful'
            ];
        } catch (\Exception $e) {
            $status = 'degraded';
            $checks['redis'] = [
                'status' => 'unhealthy',
                'message' => 'Redis connection failed: ' . $e->getMessage()
            ];
        }

        // Storage check
        try {
            $testFile = storage_path('app/health-check-test.txt');
            file_put_contents($testFile, 'test');
            unlink($testFile);
            $checks['storage'] = [
                'status' => 'healthy',
                'message' => 'Storage write/read successful'
            ];
        } catch (\Exception $e) {
            $status = 'unhealthy';
            $checks['storage'] = [
                'status' => 'unhealthy',
                'message' => 'Storage check failed: ' . $e->getMessage()
            ];
        }

        $httpStatus = $status === 'healthy' ? 200 : ($status === 'degraded' ? 200 : 503);

        return response()->json([
            'status' => $status,
            'timestamp' => now()->toISOString(),
            'version' => config('app.version', '1.0.0'),
            'environment' => config('app.env'),
            'checks' => $checks
        ], $httpStatus);
    }

    /**
     * @OA\Get(
     *     path="/ping",
     *     tags={"Health"},
     *     summary="Simple ping endpoint",
     *     description="Endpoint simples para verificar se a API está respondendo",
     *     @OA\Response(
     *         response=200,
     *         description="Pong response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="pong"),
     *             @OA\Property(property="timestamp", type="string", format="date-time")
     *         )
     *     )
     * )
     *
     * Simple ping endpoint
     *
     * @return JsonResponse
     */
    public function ping(): JsonResponse
    {
        return response()->json([
            'message' => 'pong',
            'timestamp' => now()->toISOString()
        ]);
    }
}
