<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $methods = auth()->user()->paymentMethods()->paginate();
        return response()->json($methods);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|string',
            'provider' => 'required|string',
            'token' => 'required|string',
            'details' => 'nullable|array',
            'is_default' => 'boolean',
        ]);
        if (!empty($data['is_default'])) {
            auth()->user()->paymentMethods()->update(['is_default' => false]);
        }
        $method = auth()->user()->paymentMethods()->create($data);
        return response()->json($method, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $method = auth()->user()->paymentMethods()->findOrFail($id);
        return response()->json($method);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $method = auth()->user()->paymentMethods()->findOrFail($id);
        $data = $request->validate([
            'details' => 'nullable|array',
            'is_default' => 'boolean',
        ]);
        if (!empty($data['is_default'])) {
            auth()->user()->paymentMethods()->update(['is_default' => false]);
        }
        $method->update($data);
        return response()->json($method);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $method = auth()->user()->paymentMethods()->findOrFail($id);
        $method->delete();
        return response()->noContent();
    }
}
