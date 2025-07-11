<?php

namespace App\Infrastructure\Security;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SecurityLogger
{
    public static function logSecurityEvent(string $event, array $context = [], string $level = 'warning'): void
    {
        $securityContext = [
            'event_type' => 'security',
            'event_name' => $event,
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
            'ip_address' => self::getClientIp(),
            'user_agent' => request()->userAgent(),
            'request_id' => request()->header('X-Request-ID') ?? uniqid('req_'),
        ];

        $context = array_merge($securityContext, $context);

        // Remove sensitive data
        $context = self::sanitizeContext($context);

        Log::log($level, "Security Event: {$event}", $context);
    }

    public static function logAuthenticationAttempt(string $email, bool $successful, string $reason = ''): void
    {
        self::logSecurityEvent('authentication_attempt', [
            'email' => self::maskEmail($email),
            'successful' => $successful,
            'reason' => $reason,
        ]);
    }

    public static function logSuspiciousActivity(string $description, array $details = []): void
    {
        self::logSecurityEvent('suspicious_activity', [
            'description' => $description,
            'details' => $details,
        ], 'error');
    }

    public static function logRateLimitExceeded(string $resource, int $attempts): void
    {
        self::logSecurityEvent('rate_limit_exceeded', [
            'resource' => $resource,
            'attempts' => $attempts,
        ]);
    }

    private static function getClientIp(): string
    {
        $request = request();
        
        if ($request) {
            return $request->ip();
        }
        
        return 'unknown';
    }

    private static function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        
        if (count($parts) !== 2) {
            return 'invalid_email';
        }
        
        $username = $parts[0];
        $domain = $parts[1];
        
        if (strlen($username) <= 2) {
            $maskedUsername = str_repeat('*', strlen($username));
        } else {
            $maskedUsername = substr($username, 0, 2) . str_repeat('*', strlen($username) - 2);
        }
        
        return $maskedUsername . '@' . $domain;
    }

    private static function sanitizeContext(array $context): array
    {
        $sensitiveKeys = [
            'password',
            'token',
            'secret',
            'key',
            'authorization',
            'credit_card',
            'ssn',
            'cpf',
        ];

        foreach ($sensitiveKeys as $key) {
            if (isset($context[$key])) {
                $context[$key] = '[REDACTED]';
            }
        }

        return $context;
    }
}
