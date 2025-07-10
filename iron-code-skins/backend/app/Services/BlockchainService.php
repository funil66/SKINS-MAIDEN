<?php

namespace App\Services;

use Exception;

class BlockchainService
{
    protected $apiUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->apiUrl = env('BLOCKCHAIN_API_URL');
        $this->apiKey = env('BLOCKCHAIN_API_KEY');
    }

    public function createTransaction($data)
    {
        try {
            // Logic to create a transaction on the blockchain
            $response = $this->sendRequest('POST', '/transactions', $data);
            return $response;
        } catch (Exception $e) {
            // Handle exceptions
            throw new Exception('Error creating transaction: ' . $e->getMessage());
        }
    }

    public function getTransactionStatus($transactionId)
    {
        try {
            // Logic to get the status of a transaction
            $response = $this->sendRequest('GET', '/transactions/' . $transactionId);
            return $response;
        } catch (Exception $e) {
            // Handle exceptions
            throw new Exception('Error fetching transaction status: ' . $e->getMessage());
        }
    }

    protected function sendRequest($method, $endpoint, $data = [])
    {
        // Logic to send HTTP requests to the blockchain API
        $client = new \GuzzleHttp\Client();
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => $data,
        ];

        $response = $client->request($method, $this->apiUrl . $endpoint, $options);
        return json_decode($response->getBody(), true);
    }
}