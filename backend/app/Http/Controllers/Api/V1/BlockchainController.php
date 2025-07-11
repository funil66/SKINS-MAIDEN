<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BlockchainController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $records = \App\Models\BlockchainRecord::latest()->paginate();
        return response()->json($records);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|string',
            'reference_id' => 'required|integer',
            'payload' => 'nullable|array',
            'blockchain' => 'sometimes|string',
        ]);
        $service = new \App\Services\Blockchain\BlockchainService();
        $record = $service->register(
            $data['type'],
            $data['reference_id'],
            $data['payload'] ?? [],
            $data['blockchain'] ?? 'ethereum'
        );
        return response()->json($record, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $record = \App\Models\BlockchainRecord::findOrFail($id);
        return response()->json($record);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $record = \App\Models\BlockchainRecord::findOrFail($id);
        $record->delete();
        return response()->noContent();
    }
}
