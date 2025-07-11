<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    /**
     * Buscar mensagens de um contrato
     */
    public function fetchMessages(Request $request, $contractId): JsonResponse
    {
        $user = $request->user();
        
        // Verificar se o usuário tem acesso ao contrato
        $contract = Contract::whereHas('parties', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($contractId);

        $messages = Message::forContract($contractId)
            ->with(['sender:id,name,avatar'])
            ->orderBy('created_at', 'asc')
            ->paginate($request->get('per_page', 50));

        // Marcar mensagens como lidas (exceto as enviadas pelo próprio usuário)
        Message::forContract($contractId)
            ->where('sender_id', '!=', $user->id)
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json([
            'status' => 'success',
            'data' => $messages->items(),
            'meta' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ]
        ]);
    }

    /**
     * Enviar uma nova mensagem
     */
    public function sendMessage(Request $request, $contractId): JsonResponse
    {
        $user = $request->user();
        
        // Verificar se o usuário tem acesso ao contrato
        $contract = Contract::whereHas('parties', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($contractId);

        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:2000',
            'type' => 'sometimes|string|in:text,image,file',
            'metadata' => 'sometimes|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $message = Message::create([
            'contract_id' => $contractId,
            'sender_id' => $user->id,
            'content' => $request->content,
            'type' => $request->get('type', 'text'),
            'metadata' => $request->get('metadata', [])
        ]);

        // Carregar relacionamentos para a resposta
        $message->load(['sender:id,name,avatar']);

        // Aqui seria onde dispararíamos o evento para broadcasting
        // broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'status' => 'success',
            'data' => $message
        ], 201);
    }

    /**
     * Obter contagem de mensagens não lidas por contrato
     */
    public function getUnreadCounts(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Obter todos os contratos do usuário
        $contractIds = Contract::whereHas('parties', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->pluck('id');

        $unreadCounts = Message::whereIn('contract_id', $contractIds)
            ->where('sender_id', '!=', $user->id)
            ->unread()
            ->selectRaw('contract_id, COUNT(*) as unread_count')
            ->groupBy('contract_id')
            ->get()
            ->pluck('unread_count', 'contract_id');

        return response()->json([
            'status' => 'success',
            'data' => $unreadCounts
        ]);
    }
}
