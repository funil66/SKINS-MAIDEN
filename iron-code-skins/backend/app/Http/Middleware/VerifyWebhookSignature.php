<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyWebhookSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Retrieve the signature from the request headers
        $signature = $request->header('X-Signature');

        // Retrieve the payload from the request
        $payload = $request->getContent();

        // Verify the signature
        if (!$this->isValidSignature($payload, $signature)) {
            Log::warning('Invalid webhook signature.', ['payload' => $payload]);
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        return $next($request);
    }

    /**
     * Validate the webhook signature.
     *
     * @param  string  $payload
     * @param  string  $signature
     * @return bool
     */
    protected function isValidSignature($payload, $signature)
    {
        // Here you would implement your signature verification logic
        // For example, using HMAC with a secret key
        $secret = config('services.webhook.secret'); // Retrieve your secret from config
        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }
}