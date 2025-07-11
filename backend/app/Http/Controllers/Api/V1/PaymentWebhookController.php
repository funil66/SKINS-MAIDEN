<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Events\PaymentCompleted;
use App\Models\Payment;

class PaymentWebhookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $payload = $request->all();
        // Example for MercadoPago webhook
        if (isset($payload['type']) && $payload['type'] === 'payment') {
            $paymentId = $payload['data']['id'] ?? null;
            if ($paymentId) {
                // Dispatch event after verifying status
                event(new PaymentCompleted(App\Models\Payment::where('gateway_payment_id', $paymentId)->first()));
            }
        }
        return response()->noContent();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
