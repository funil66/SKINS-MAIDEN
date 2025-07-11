<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReputationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reputations = \App\Models\Reputation::with('user')->paginate();
        return response()->json($reputations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);
        $reputation = \App\Models\Reputation::updateOrCreate(
            ['user_id' => $data['user_id']],
            ['total_reviews' => 0, 'average_score' => 0]
        );
        return response()->json($reputation, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $reputation = \App\Models\Reputation::with('user')->findOrFail($id);
        return response()->json($reputation);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $reputation = \App\Models\Reputation::findOrFail($id);
        $data = $request->validate([
            'total_reviews' => 'integer|min:0',
            'average_score' => 'numeric|min:0|max:5',
            'meta' => 'nullable|array',
        ]);
        $reputation->update($data);
        return response()->json($reputation);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $reputation = \App\Models\Reputation::findOrFail($id);
        $reputation->delete();
        return response()->noContent();
    }
}
