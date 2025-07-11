<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Domain\Contracts\Services\ContractGenerationService;
use App\Domain\Contracts\Services\DigitalSignatureService;
use App\Domain\Contracts\Repositories\ContractRepositoryInterface;
use App\Http\Requests\Contracts\CreateContractRequest;
use App\Http\Requests\Contracts\SignContractRequest;
use App\Http\Requests\Contracts\UpdateContractRequest;
use App\Http\Resources\ContractResource;
use App\Http\Resources\ContractSignatureResource;
use App\Models\Contract as ContractModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Contract;

class ContractsController extends Controller
{
    public function __construct(
        private ContractGenerationService $contractService,
        private DigitalSignatureService $signatureService,
        private ContractRepositoryInterface $contractRepository
    ) {}

    /**
     * Listar contratos do usuário
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'status' => $request->get('status'),
            'template_id' => $request->get('template_id'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'search' => $request->get('search')
        ];

        // Filtrar por usuário atual se não for admin
        if (!$request->user()->hasRole('admin')) {
            $filters['user_id'] = $request->user()->id;
        }

        $contracts = $this->contractRepository->search(
            array_filter($filters),
            $request->get('page', 1),
            $request->get('per_page', 15)
        );

        return response()->json([
            'success' => true,
            'data' => ContractResource::collection($contracts->items()),
            'meta' => [
                'current_page' => $contracts->currentPage(),
                'per_page' => $contracts->perPage(),
                'total' => $contracts->total(),
                'last_page' => $contracts->lastPage()
            ]
        ]);
    }

    /**
     * Criar novo contrato
     */
    public function store(CreateContractRequest $request): JsonResponse
    {
        try {
            if ($request->template_id) {
                // Gerar a partir de template
                $contract = $this->contractService->generateFromTemplate(
                    $request->template_id,
                    $request->transaction_id,
                    $request->buyer_id,
                    $request->seller_id,
                    $request->variables ?? []
                );
            } else {
                // Contrato personalizado
                $contract = $this->contractService->generateCustomContract(
                    $request->transaction_id,
                    $request->buyer_id,
                    $request->seller_id,
                    $request->content,
                    $request->metadata ?? []
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Contrato criado com sucesso',
                'data' => new ContractResource($contract)
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar contrato: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Exibir contrato específico
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $contract = $this->contractRepository->findById($id);

        if (!$contract) {
            return response()->json([
                'success' => false,
                'message' => 'Contrato não encontrado'
            ], Response::HTTP_NOT_FOUND);
        }

        // Verificar permissão
        if (!$this->canUserAccessContract($request->user(), $contract)) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado'
            ], Response::HTTP_FORBIDDEN);
        }

        return response()->json([
            'success' => true,
            'data' => new ContractResource($contract)
        ]);
    }

    /**
     * Atualizar contrato
     */
    public function update(UpdateContractRequest $request, string $id): JsonResponse
    {
        try {
            $contract = $this->contractRepository->findById($id);

            if (!$contract) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contrato não encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            // Verificar se pode ser editado
            if (!in_array($contract->status, ['draft'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contrato não pode ser editado no status atual'
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->contractRepository->update($id, $request->validated());

            $updatedContract = $this->contractRepository->findById($id);

            return response()->json([
                'success' => true,
                'message' => 'Contrato atualizado com sucesso',
                'data' => new ContractResource($updatedContract)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar contrato: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Finalizar contrato para assinatura
     */
    public function finalize(string $id): JsonResponse
    {
        try {
            $contract = $this->contractService->finalizeForSignature($id);

            return response()->json([
                'success' => true,
                'message' => 'Contrato finalizado e pronto para assinatura',
                'data' => new ContractResource($contract)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao finalizar contrato: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Assinar contrato
     */
    public function sign(SignContractRequest $request, string $id): JsonResponse
    {
        try {
            $signature = $this->signatureService->signContract(
                $id,
                $request->user()->id,
                $request->signer_type,
                $request->digital_signature,
                $request->metadata ?? []
            );

            return response()->json([
                'success' => true,
                'message' => 'Contrato assinado com sucesso',
                'data' => new ContractSignatureResource($signature)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao assinar contrato: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Obter assinaturas do contrato
     */
    public function signatures(string $id, Request $request): JsonResponse
    {
        $contract = $this->contractRepository->findById($id);

        if (!$contract) {
            return response()->json([
                'success' => false,
                'message' => 'Contrato não encontrado'
            ], Response::HTTP_NOT_FOUND);
        }

        // Verificar permissão
        if (!$this->canUserAccessContract($request->user(), $contract)) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado'
            ], Response::HTTP_FORBIDDEN);
        }

        $signatures = $this->signatureService->getContractSignatures($id);

        return response()->json([
            'success' => true,
            'data' => $signatures
        ]);
    }

    /**
     * Verificar assinatura
     */
    public function verifySignature(string $contractId, string $signatureId): JsonResponse
    {
        try {
            $verification = $this->signatureService->verifySignature($signatureId);

            return response()->json([
                'success' => true,
                'data' => $verification
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar assinatura: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Invalidar assinatura (admin only)
     */
    public function invalidateSignature(Request $request, string $contractId, string $signatureId): JsonResponse
    {
        if (!$request->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado'
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            $this->signatureService->invalidateSignature(
                $signatureId,
                $request->get('reason', 'Invalidada por administrador'),
                $request->user()->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Assinatura invalidada com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao invalidar assinatura: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Obter estatísticas de contratos
     */
    public function statistics(Request $request): JsonResponse
    {
        $filters = [
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to')
        ];

        $stats = $this->contractRepository->getStatistics(array_filter($filters));

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Listar apenas os contratos do usuário autenticado
     */
    public function myTransactions(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $contracts = ContractModel::whereHas('parties', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->with(['template', 'signatures', 'parties.user'])
        ->when($request->get('status'), function($query, $status) {
            return $query->where('status', $status);
        })
        ->when($request->get('search'), function($query, $search) {
            return $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        })
        ->orderBy('created_at', 'desc')
        ->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => ContractResource::collection($contracts),
            'meta' => [
                'current_page' => $contracts->currentPage(),
                'last_page' => $contracts->lastPage(),
                'per_page' => $contracts->perPage(),
                'total' => $contracts->total(),
            ]
        ]);
    }

    /**
     * Verificar se usuário pode acessar o contrato
     */
    private function canUserAccessContract($user, $contract): bool
    {
        // Admin pode acessar todos
        if ($user->hasRole('admin')) {
            return true;
        }

        // Usuário pode acessar se for comprador ou vendedor
        return $contract->buyer_id === $user->id || $contract->seller_id === $user->id;
    }
}
