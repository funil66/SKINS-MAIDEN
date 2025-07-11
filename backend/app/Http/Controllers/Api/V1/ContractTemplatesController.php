<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Domain\Contracts\Repositories\ContractTemplateRepositoryInterface;
use App\Http\Requests\ContractTemplates\CreateTemplateRequest;
use App\Http\Requests\ContractTemplates\UpdateTemplateRequest;
use App\Http\Resources\ContractTemplateResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ContractTemplatesController extends Controller
{
    public function __construct(
        private ContractTemplateRepositoryInterface $templateRepository
    ) {}

    /**
     * Listar templates de contrato
     */
    public function index(Request $request): JsonResponse
    {
        $filters = [
            'category' => $request->get('category'),
            'is_active' => $request->get('is_active'),
            'search' => $request->get('search')
        ];

        // Filtrar por criador se não for admin
        if (!$request->user()->hasRole('admin')) {
            $filters['created_by'] = $request->user()->id;
        }

        $templates = $this->templateRepository->search(
            array_filter($filters),
            $request->get('page', 1),
            $request->get('per_page', 15)
        );

        return response()->json([
            'success' => true,
            'data' => ContractTemplateResource::collection($templates->items()),
            'meta' => [
                'current_page' => $templates->currentPage(),
                'per_page' => $templates->perPage(),
                'total' => $templates->total(),
                'last_page' => $templates->lastPage()
            ]
        ]);
    }

    /**
     * Listar templates ativos
     */
    public function active(): JsonResponse
    {
        $templates = $this->templateRepository->findActive();

        return response()->json([
            'success' => true,
            'data' => ContractTemplateResource::collection($templates)
        ]);
    }

    /**
     * Listar templates por categoria
     */
    public function byCategory(string $category): JsonResponse
    {
        $templates = $this->templateRepository->findByCategory($category);

        return response()->json([
            'success' => true,
            'data' => ContractTemplateResource::collection($templates)
        ]);
    }

    /**
     * Criar novo template
     */
    public function store(CreateTemplateRequest $request): JsonResponse
    {
        try {
            $template = $this->templateRepository->create(array_merge(
                $request->validated(),
                ['created_by' => $request->user()->id]
            ));

            return response()->json([
                'success' => true,
                'message' => 'Template criado com sucesso',
                'data' => new ContractTemplateResource($template)
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar template: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Exibir template específico
     */
    public function show(string $id): JsonResponse
    {
        $template = $this->templateRepository->findById($id);

        if (!$template) {
            return response()->json([
                'success' => false,
                'message' => 'Template não encontrado'
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'data' => new ContractTemplateResource($template)
        ]);
    }

    /**
     * Atualizar template
     */
    public function update(UpdateTemplateRequest $request, string $id): JsonResponse
    {
        try {
            $template = $this->templateRepository->findById($id);

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template não encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            $this->templateRepository->update($id, $request->validated());

            $updatedTemplate = $this->templateRepository->findById($id);

            return response()->json([
                'success' => true,
                'message' => 'Template atualizado com sucesso',
                'data' => new ContractTemplateResource($updatedTemplate)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar template: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Desativar template
     */
    public function deactivate(string $id): JsonResponse
    {
        try {
            $template = $this->templateRepository->findById($id);

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template não encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            $this->templateRepository->update($id, ['is_active' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Template desativado com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao desativar template: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Ativar template
     */
    public function activate(string $id): JsonResponse
    {
        try {
            $template = $this->templateRepository->findById($id);

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template não encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            $this->templateRepository->update($id, ['is_active' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Template ativado com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao ativar template: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Criar nova versão do template
     */
    public function createVersion(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'content' => 'required|string',
            'variables' => 'sometimes|array',
            'description' => 'sometimes|string'
        ]);

        try {
            $template = $this->templateRepository->findById($id);

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template não encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            $newVersion = $this->templateRepository->createVersion($id, [
                'content' => $request->content,
                'variables' => $request->variables ?? $template->variables,
                'description' => $request->description ?? $template->description,
                'created_by' => $request->user()->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nova versão criada com sucesso',
                'data' => new ContractTemplateResource($newVersion)
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar versão: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Listar versões do template
     */
    public function versions(string $id): JsonResponse
    {
        try {
            $versions = $this->templateRepository->getVersions($id);

            return response()->json([
                'success' => true,
                'data' => ContractTemplateResource::collection($versions)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar versões: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Obter estatísticas de uso do template
     */
    public function usage(string $id): JsonResponse
    {
        try {
            $stats = $this->templateRepository->getUsageStatistics($id);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar estatísticas: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Duplicar template
     */
    public function duplicate(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        try {
            $original = $this->templateRepository->findById($id);

            if (!$original) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template não encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            $duplicate = $this->templateRepository->create([
                'name' => $request->name,
                'description' => $original->description,
                'category' => $original->category,
                'content' => $original->content,
                'variables' => $original->variables,
                'version' => '1.0',
                'is_active' => false, // Inativo por padrão
                'created_by' => $request->user()->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Template duplicado com sucesso',
                'data' => new ContractTemplateResource($duplicate)
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao duplicar template: ' . $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
