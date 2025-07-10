<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KYCDocument;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class KYCController extends Controller
{
    /**
     * Store a new KYC document for a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'document_type' => 'required|string',
            'document_data' => 'required|file|mimes:jpg,png,pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $document = new KYCDocument();
        $document->user_id = $request->user_id;
        $document->document_type = $request->document_type;

        // Store the document file
        $path = $request->file('document_data')->store('kyc_documents');
        $document->document_data = $path;

        $document->save();

        return response()->json(['message' => 'KYC document uploaded successfully.'], 201);
    }

    /**
     * Retrieve KYC documents for a specific user.
     *
     * @param  int  $userId
     * @return \Illuminate\Http\Response
     */
    public function show($userId)
    {
        $documents = KYCDocument::where('user_id', $userId)->get();

        if ($documents->isEmpty()) {
            return response()->json(['message' => 'No KYC documents found for this user.'], 404);
        }

        return response()->json($documents, 200);
    }

    /**
     * Delete a KYC document.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $document = KYCDocument::find($id);

        if (!$document) {
            return response()->json(['message' => 'KYC document not found.'], 404);
        }

        // Delete the document file from storage
        \Storage::delete($document->document_data);
        $document->delete();

        return response()->json(['message' => 'KYC document deleted successfully.'], 200);
    }
}