<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PrivacyController extends Controller
{
    /**
     * Lista todas as solicitações LGPD do usuário autenticado.
     */
    public function index()
    {
        $requests = auth()->user()->dataSubjectRequests()->latest()->paginate();
        return response()->json($requests);
    }

    /**
     * Cria uma nova solicitação LGPD.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:access,rectification,erasure,portability,objection',
            'description' => 'nullable|string|max:1000',
        ]);
        
        $data['user_id'] = auth()->id();
        $subjectRequest = \App\Models\DataSubjectRequest::create($data);
        
        return response()->json($subjectRequest, 201);
    }

    /**
     * Mostra uma solicitação específica.
     */
    public function show(string $id)
    {
        $request = auth()->user()->dataSubjectRequests()->findOrFail($id);
        return response()->json($request);
    }

    /**
     * Retorna a política de privacidade ativa.
     */
    public function policy()
    {
        $policy = \App\Models\PrivacyPolicy::getActive();
        return response()->json($policy);
    }

    /**
     * Exporta todos os dados do usuário (direito à portabilidade).
     */
    public function exportData()
    {
        $user = auth()->user();
        $data = [
            'user' => $user->toArray(),
            'contracts' => $user->contracts()->get()->toArray(),
            'payments' => $user->payments()->get()->toArray(),
            'reviews_given' => $user->reviewsGiven()->get()->toArray(),
            'reviews_received' => $user->reviewsReceived()->get()->toArray(),
            'reputation' => $user->reputation()->first()?->toArray(),
            'messages' => $user->messages()->get()->toArray(),
            'exported_at' => now()->toISOString(),
        ];
        
        return response()->json($data);
    }

    /**
     * Solicita apagamento de conta (direito ao esquecimento).
     */
    public function requestDeletion(Request $request)
    {
        $data = $request->validate([
            'confirmation' => 'required|string|in:DELETE_MY_ACCOUNT',
            'reason' => 'nullable|string|max:500',
        ]);
        
        $subjectRequest = \App\Models\DataSubjectRequest::create([
            'user_id' => auth()->id(),
            'type' => 'erasure',
            'description' => $data['reason'] ?? 'Solicitação de apagamento de conta',
        ]);
        
        return response()->json([
            'message' => 'Solicitação de apagamento registrada. Será processada em até 30 dias.',
            'request_id' => $subjectRequest->id,
        ]);
    }
}
