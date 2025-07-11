<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Payment;
use App\Services\Payment\PaymentGatewayInterface;
use App\Events\PaymentCompleted;
use App\Events\PaymentFailed;

class PaymentController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payments = Auth::user()->payments()->paginate();
        return response()->json($payments);
    }

    /**
     * Store a newly created payment and initiate with gateway.
     */
    public function store(Request $request, PaymentGatewayInterface $gateway)
    {
        $data = $request->validate([
            'contract_id' => 'required|exists:contracts,id',
            'amount' => 'required|numeric',
            'currency' => 'sometimes|string',
            'method_id' => 'required|string',
            'description' => 'sometimes|string',
        ]);
        $gatewayResult = $gateway->createPayment($data);
        $payment = Payment::create([
            'user_id' => Auth::id(),
            'contract_id' => $data['contract_id'],
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'BRL',
            'payment_gateway' => $gatewayResult['gateway'] ?? 'mercadopago',
            'gateway_payment_id' => $gatewayResult['id'],
            'status' => $gatewayResult['status'],
            'metadata' => $gatewayResult,
        ]);
        return response()->json($payment, 201);
    }

    /**
     * Display the specified payment and status.
     */
    public function show(string $id, PaymentGatewayInterface $gateway)
    {
        $payment = Payment::findOrFail($id);
        $status = $gateway->getPaymentStatus($payment->gateway_payment_id);
        $payment->status = $status['status'];
        $payment->save();
        return response()->json($payment);
    }

    /**
     * Confirm the payment manually via gateway.
     */
    public function confirm(Request $request, string $id, PaymentGatewayInterface $gateway)
    {
        $payment = Payment::findOrFail($id);
        $result = $gateway->getPaymentStatus($payment->gateway_payment_id);
        $payment->status = $result['status'];
        $payment->save();
        if ($payment->status === 'completed') {
            event(new PaymentCompleted($payment));
        } elseif ($payment->status === 'failed') {
            event(new PaymentFailed($payment));
        }
        return response()->json($payment);
    }

    /**
     * Delete a payment record.
     */
    public function destroy(string $id)
    {
        $payment = Payment::findOrFail($id);
        $payment->delete();
        return response()->noContent();
    }
}
