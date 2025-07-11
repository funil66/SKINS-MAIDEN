<?php

namespace App\Services\Blockchain;

use App\Models\BlockchainRecord;

class BlockchainService
{
    /**
     * Simula o registro de um dado na blockchain (mock para demo).
     */
    public function register(string $type, int $referenceId, array $payload = [], string $blockchain = 'ethereum') : BlockchainRecord
    {
        // Aqui integraria com API real (ex: Infura, BlockCypher)
        $txHash = '0x' . bin2hex(random_bytes(16));
        return BlockchainRecord::create([
            'type' => $type,
            'reference_id' => $referenceId,
            'blockchain' => $blockchain,
            'tx_hash' => $txHash,
            'status' => 'confirmed',
            'payload' => $payload,
        ]);
    }

    /**
     * Consulta um registro blockchain pelo hash.
     */
    public function getByHash(string $txHash): ?BlockchainRecord
    {
        return BlockchainRecord::where('tx_hash', $txHash)->first();
    }
}
