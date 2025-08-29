<?php

namespace App\Actions;

use App\Models\Game;
use App\Services\RestGamesService;

class UpdatePlayerCountAction
{
    private RestGamesService $restGamesService;

    public function __construct(RestGamesService $restGamesService)
    {
        $this->restGamesService = $restGamesService;
    }

    /**
     * @param Game $game
     * @return bool
     */
    public function execute(Game $game): bool
    {
        $playerCount = $this->restGamesService->getPlayerCount($game->appid);

        if (is_int($playerCount)) {
            $game->player_count = $playerCount;
            if ($playerCount > 0) {
                $game->touch();
            }
            $game->save();
            return true;
        }

        Log::info("No se obtuvo contador de jugadores para el juego '{$game->name}' (AppID: {$game->appid}).");

        return false;
    }
}
