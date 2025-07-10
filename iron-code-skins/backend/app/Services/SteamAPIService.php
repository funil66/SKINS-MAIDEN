<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class SteamAPIService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = env('STEAM_API_KEY'); // Ensure to set your Steam API key in the .env file
    }

    public function getUserProfile($steamId)
    {
        $url = "https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key={$this->apiKey}&steamids={$steamId}";

        try {
            $response = $this->client->get($url);
            $data = json_decode($response->getBody(), true);

            return $data['response']['players'][0] ?? null;
        } catch (RequestException $e) {
            // Handle the exception (log it, rethrow it, etc.)
            return null;
        }
    }

    public function getUserInventory($steamId, $appId = 730)
    {
        $url = "https://api.steampowered.com/IEconItems_$appId/GetPlayerItems/v0001/?key={$this->apiKey}&steamid={$steamId}";

        try {
            $response = $this->client->get($url);
            $data = json_decode($response->getBody(), true);

            return $data['result']['items'] ?? [];
        } catch (RequestException $e) {
            // Handle the exception (log it, rethrow it, etc.)
            return [];
        }
    }
}