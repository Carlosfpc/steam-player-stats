<?php

namespace App\Services;

use Illuminate\Http\Client\Factory as Http;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;

class RestGamesService
{

    private const API_BASE_URL = 'https://api.steampowered.com';

    private Http $http;

    public function __construct(Http $http)
    {
        $this->http = $http;
    }

    /**
     * @return Collection
     * @throws RequestException
     */
    public function getAllGames(): Collection
    {
        $response = $this->http->baseUrl(self::API_BASE_URL)
            ->get('/ISteamApps/GetAppList/v2/');

        $response->throw();

        return $response->collect('applist.apps');
    }

    /**
     * @param int $appId
     * @return int|null 
     * @throws RequestException
     */
    public function getPlayerCount(int $appId): ?int
    {
        $response = $this->http->baseUrl(self::API_BASE_URL)
            ->get('/ISteamUserStats/GetNumberOfCurrentPlayers/v1/', [
                'appid' => $appId,
            ]);

        $response->throw();

        if ($response->json('response.result') === 1) {
            return $response->json('response.player_count');
        }

        return null;
    }
}
