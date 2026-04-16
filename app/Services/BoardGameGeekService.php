<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Game;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;

class BoardGameGeekService
{
    private string $baseUrl;
    private string $token;

    public function __construct()
    {
        $this->baseUrl = config("services.bgg.base_url");
        $this->token = config("services.bgg.token");
    }

    public function importFromUrl(string $url, int $userId): Game
    {
        $bggId = $this->extractGameId($url);
        $data = $this->fetchGameData($bggId);

        return $this->createGame($data, $userId);
    }

    public function fetchPreview(string $url): array
    {
        $bggId = $this->extractGameId($url);

        return $this->fetchGameData($bggId);
    }

    private function extractGameId(string $url): int
    {
        if (!preg_match('/boardgame\/(\d+)/i', $url, $matches)) {
            throw new InvalidArgumentException(
                "Could not extract a game ID from the URL: {$url}. " .
                "Make sure it is a BoardGameGeek board game page.",
            );
        }

        return (int)$matches[1];
    }

    private function fetchGameData(int $bggId): array
    {
        try {
            $response = Http::withToken($this->token)
                ->timeout(15)
                ->get("{$this->baseUrl}/thing", [
                    "id" => $bggId,
                    "type" => "boardgame",
                ]);

            $response->throw();
        } catch (ConnectionException $exception) {
            Log::error("BGG API connection failed", ["bgg_id" => $bggId, "error" => $exception->getMessage()]);

            throw new RuntimeException("Could not connect to BoardGameGeek API. Please try again later.");
        } catch (RequestException $exception) {
            Log::error("BGG API request failed", [
                "bgg_id" => $bggId,
                "status" => $exception->response->status(),
                "body" => $exception->response->body(),
            ]);

            throw new RuntimeException("BoardGameGeek API returned an error for game ID {$bggId}.");
        }

        return $this->parseXmlResponse($response->body(), $bggId);
    }

    private function parseXmlResponse(string $xmlBody, int $bggId): array
    {
        $xml = simplexml_load_string($xmlBody, "SimpleXMLElement", LIBXML_NOCDATA);

        if ($xml === false || !isset($xml->item[0])) {
            throw new RuntimeException(
                "BoardGameGeek returned no data for game ID {$bggId}. " .
                "The game may not exist or may have been removed.",
            );
        }

        $item = $xml->item[0];

        $primaryName = "";

        foreach ($item->name as $name) {
            if ((string)$name["type"] === "primary") {
                $primaryName = (string)$name["value"];

                break;
            }
        }

        $description = html_entity_decode(
            (string)$item->description,
            ENT_QUOTES | ENT_HTML5,
            "UTF-8",
        );

        return [
            "bgg_id" => $bggId,
            "name" => $primaryName,
            "description" => $description,
            "min_players" => (int)$item->minplayers["value"],
            "max_players" => (int)$item->maxplayers["value"],
            "min_age" => (int)$item->minage["value"],
            "year" => (int)$item->yearpublished["value"],
            "bgg_url" => "https://boardgamegeek.com/boardgame/{$bggId}",
        ];
    }

    private function createGame(array $data, int $userId): Game
    {
        return Game::create([
            "user_id" => $userId,
            "name" => $data["name"],
            "description" => $data["description"],
            "min_players" => $data["min_players"],
            "max_players" => $data["max_players"],
            "is_shared" => false,
            "bgg_id" => $data["bgg_id"],
            "bgg_url" => $data["bgg_url"],
            "year" => $data["year"],
            "min_age" => $data["min_age"],
        ]);
    }
}
