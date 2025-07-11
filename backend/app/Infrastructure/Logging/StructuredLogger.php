<?php

namespace App\Infrastructure\Logging;

use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Processor\WebProcessor;

class StructuredLogger
{
    public static function create(string $name): Logger
    {
        $logger = new Logger($name);

        // JSON formatter for structured logging
        $formatter = new JsonFormatter();
        
        // Stream handler for stdout (Docker-friendly)
        $handler = new StreamHandler('php://stdout', Logger::DEBUG);
        $handler->setFormatter($formatter);
        
        $logger->pushHandler($handler);
        
        // Add processors for additional context
        $logger->pushProcessor(new PsrLogMessageProcessor());
        $logger->pushProcessor(new WebProcessor());
        $logger->pushProcessor(function ($record) {
            $record['extra']['application'] = 'iron-code-skins';
            $record['extra']['version'] = config('app.version', '1.0.0');
            $record['extra']['environment'] = config('app.env');
            $record['extra']['timestamp_iso'] = now()->toISOString();
            
            return $record;
        });
        
        return $logger;
    }
}
