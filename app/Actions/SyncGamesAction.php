<?php

namespace App\Actions;

use App\Models\Game;
use App\Services\RestGamesService;
use Illuminate\Support\Facades\Log;

class SyncGamesAction
{
    private RestGamesService $restGamesService;

    public function __construct(RestGamesService $restGamesService)
    {
        $this->restGamesService = $restGamesService;
    }

    /**
     * Ejecuta el proceso de sincronización.
     *
     * @return array
     */
    public function execute(): array
    {
        try {
            $gamesFromApi = $this->restGamesService->getAllGames();

            $validGames = $gamesFromApi->filter(function ($game) {
                return !empty($game['name']);
            });

            $totalFetched = $validGames->count();

            if ($totalFetched === 0) {
                return ['total_fetched' => 0, 'upserted' => 0, 'status' => 'No se obtuvieron juegos válidos de la API.'];
            }

            $upsertedCount = 0;

            foreach ($validGames as $gameData) {
                Game::updateOrCreate(
                    ['appid' => $gameData['appid']],
                    ['name' => $gameData['name']]
                );
                $upsertedCount++;
            }

            return [
                'total_fetched' => $totalFetched,
                'upserted' => $upsertedCount,
                'status' => 'Sincronización completada con éxito.'
            ];

        } catch (\Throwable $e) {
            Log::error('La sincronización de juegos falló: ' . $e->getMessage());

            return [
                'total_fetched' => 0,
                'upserted' => 0,
                'status' => 'La sincronización falló. Revisa los logs para más detalles.'
            ];
        }
    }
}
