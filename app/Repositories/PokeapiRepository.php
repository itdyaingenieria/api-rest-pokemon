<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class PokeapiRepository
{
    protected $client;
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('pokeapi.base_url', 'https://pokeapi.co/api/v2');

        // Ensure base URL ends with /
        if (!str_ends_with($this->baseUrl, '/')) {
            $this->baseUrl .= '/';
        }

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout'  => (float) config('pokeapi.requests.timeout', 10),
            'headers' => [
                'User-Agent' => config('pokeapi.requests.user_agent', 'Laravel-PokeAPI-Client/1.0'),
                'Accept' => 'application/json',
            ],
        ]);

        // Log the configuration for debugging
        Log::debug('PokeAPI Repository initialized', [
            'base_url' => $this->baseUrl,
            'timeout' => config('pokeapi.requests.timeout', 10)
        ]);
    }

    /**
     * Get paginated list of pokemon names and urls from PokeAPI
     * Follows PokeAPI best practices: default 20 items, max 100 per page
     * Cache responses for better performance and to respect fair use policy
     */
    public function list(int $limit = 20, int $offset = 0): array
    {
        // Enforce PokeAPI limits: max 100 items per request
        $limit = min($limit, 100);
        $offset = max($offset, 0);

        $cacheKey = "pokeapi:list:{$limit}:{$offset}";
        $ttl = (int) config('pokeapi.cache.list_ttl', 60); // Cache for 1 hour

        return Cache::remember($cacheKey, now()->addMinutes($ttl), function () use ($limit, $offset) {
            try {
                $response = $this->client->get('pokemon', [
                    'query' => [
                        'limit' => $limit,
                        'offset' => $offset,
                    ],
                ]);

                $data = json_decode($response->getBody()->getContents(), true);

                // Validate response structure
                if (!isset($data['results']) || !is_array($data['results'])) {
                    Log::error('Invalid response structure from PokeAPI', ['data' => $data]);
                    return ['count' => 0, 'results' => []];
                }

                return $data;
            } catch (RequestException $e) {
                Log::error('PokeAPI request failed', [
                    'endpoint' => '/pokemon',
                    'limit' => $limit,
                    'offset' => $offset,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        });
    }

    /**
     * Get pokemon detail by id or name
     * Supports both numeric ID and string name as per PokeAPI docs
     */
    public function get(string|int $id): array
    {
        $cacheKey = "pokeapi:detail:" . strtolower((string)$id);
        $ttl = (int) config('pokeapi.cache.detail_ttl', 120); // Cache for 2 hours

        return Cache::remember($cacheKey, now()->addMinutes($ttl), function () use ($id) {
            try {
                $response = $this->client->get("pokemon/{$id}");
                $data = json_decode($response->getBody()->getContents(), true);

                // Validate essential pokemon data structure
                if (!isset($data['name']) || !isset($data['id'])) {
                    Log::warning('PokeAPI returned unexpected pokemon structure', ['id' => $id]);
                }

                return $data;
            } catch (RequestException $e) {
                Log::error('PokeAPI pokemon request failed', [
                    'id' => $id,
                    'status' => $e->getCode(),
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        });
    }

    /**
     * Get species information (contains flavor text entries / descriptions)
     * Species endpoint provides Pokédex descriptions and evolutionary data
     */
    public function getSpecies(string|int $id): array
    {
        $cacheKey = "pokeapi:species:" . strtolower((string)$id);
        $ttl = (int) config('pokeapi.cache.detail_ttl', 120); // Cache for 2 hours

        return Cache::remember($cacheKey, now()->addMinutes($ttl), function () use ($id) {
            try {
                $response = $this->client->get("pokemon-species/{$id}");
                return json_decode($response->getBody()->getContents(), true);
            } catch (RequestException $e) {
                Log::error('PokeAPI species request failed', [
                    'id' => $id,
                    'status' => $e->getCode(),
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        });
    }

    /**
     * Get a merged detailed payload convenient for favorites: name, image, description
     * Combines pokemon and species data following PokeAPI best practices
     */
    public function getDetailed(string|int $id): array
    {
        $pokemon = $this->get($id);
        $species = [];

        try {
            $species = $this->getSpecies($id);
        } catch (RequestException $e) {
            Log::warning('Could not fetch species data for pokemon', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            // Continue without species data - pokemon data is still valuable
        }

        // Extract image following PokeAPI sprite hierarchy (official artwork preferred)
        $image = $this->extractBestImage($pokemon);

        // Extract English description from species flavor text
        $description = $this->extractDescription($species);

        return [
            'id' => $pokemon['id'] ?? null,
            'name' => $pokemon['name'] ?? null,
            'image' => $image,
            'description' => $description,
            'types' => $this->extractTypes($pokemon),
            'stats' => $this->extractStats($pokemon),
            'height' => $pokemon['height'] ?? null,
            'weight' => $pokemon['weight'] ?? null,
            'abilities' => $this->extractAbilities($pokemon),
        ];
    }

    /**
     * Extract the best available image from PokeAPI sprites
     */
    private function extractBestImage(array $pokemon): ?string
    {
        // Priority order based on PokeAPI documentation recommendations
        $imagePaths = [
            'sprites.other.official-artwork.front_default',
            'sprites.other.home.front_default',
            'sprites.front_default',
            'sprites.other.dream_world.front_default',
        ];

        foreach ($imagePaths as $path) {
            $image = data_get($pokemon, $path);
            if ($image && is_string($image)) {
                return $image;
            }
        }

        return null;
    }

    /**
     * Extract English description from species flavor text entries
     */
    private function extractDescription(array $species): ?string
    {
        $flavorTexts = $species['flavor_text_entries'] ?? [];

        // Try to find English descriptions from newer games first
        $preferredVersions = ['sword', 'shield', 'sun', 'moon', 'omega-ruby', 'alpha-sapphire'];

        foreach ($preferredVersions as $version) {
            foreach ($flavorTexts as $entry) {
                if (($entry['language']['name'] ?? '') === 'en' &&
                    ($entry['version']['name'] ?? '') === $version
                ) {
                    return $this->cleanFlavorText($entry['flavor_text'] ?? '');
                }
            }
        }

        // Fallback to any English text
        foreach ($flavorTexts as $entry) {
            if (($entry['language']['name'] ?? '') === 'en') {
                return $this->cleanFlavorText($entry['flavor_text'] ?? '');
            }
        }

        return null;
    }

    /**
     * Clean and normalize flavor text
     */
    private function cleanFlavorText(string $text): string
    {
        // Remove extra whitespace and newline characters
        $text = preg_replace('/\s+/', ' ', $text);
        // Remove special characters that appear in some entries
        $text = str_replace(["\f", "\n", "\r"], ' ', $text);
        return trim($text);
    }

    /**
     * Extract pokemon types in a clean format
     */
    private function extractTypes(array $pokemon): array
    {
        $types = [];
        foreach ($pokemon['types'] ?? [] as $typeData) {
            $types[] = $typeData['type']['name'] ?? null;
        }
        return array_filter($types);
    }

    /**
     * Extract base stats
     */
    private function extractStats(array $pokemon): array
    {
        $stats = [];
        foreach ($pokemon['stats'] ?? [] as $statData) {
            $statName = $statData['stat']['name'] ?? null;
            $baseStat = $statData['base_stat'] ?? null;
            if ($statName && $baseStat !== null) {
                $stats[$statName] = $baseStat;
            }
        }
        return $stats;
    }

    /**
     * Extract abilities
     */
    private function extractAbilities(array $pokemon): array
    {
        $abilities = [];
        foreach ($pokemon['abilities'] ?? [] as $abilityData) {
            $abilities[] = [
                'name' => $abilityData['ability']['name'] ?? null,
                'is_hidden' => $abilityData['is_hidden'] ?? false,
                'slot' => $abilityData['slot'] ?? null,
            ];
        }
        return $abilities;
    }

    /**
     * Get pokemon by type - useful for filtering and searching
     */
    public function getByType(string $type): array
    {
        $cacheKey = "pokeapi:type:{$type}";
        $ttl = (int) config('pokeapi.cache.type_ttl', 240);

        return Cache::remember($cacheKey, now()->addMinutes($ttl), function () use ($type) {
            try {
                $response = $this->client->get("type/{$type}");
                return json_decode($response->getBody()->getContents(), true);
            } catch (RequestException $e) {
                Log::error('PokeAPI type request failed', [
                    'type' => $type,
                    'status' => $e->getCode(),
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        });
    }

    /**
     * Search pokemon by name (partial matching)
     * This uses the list endpoint and filters locally for better performance
     */
    public function searchByName(string $query, int $limit = 20): array
    {
        $query = strtolower(trim($query));
        if (empty($query)) {
            return $this->list($limit);
        }

        // For search, we might need to fetch more results to find matches
        $searchLimit = min($limit * 5, 1000); // Search in up to 1000 pokemon
        $allPokemon = $this->list($searchLimit);

        $matches = [];
        foreach ($allPokemon['results'] ?? [] as $pokemon) {
            if (str_contains(strtolower($pokemon['name']), $query)) {
                $matches[] = $pokemon;
                if (count($matches) >= $limit) {
                    break;
                }
            }
        }

        return [
            'count' => count($matches),
            'results' => $matches
        ];
    }

    /**
     * Get evolution chain for a pokemon species
     */
    public function getEvolutionChain(int $chainId): array
    {
        $cacheKey = "pokeapi:evolution:{$chainId}";
        $ttl = (int) config('pokeapi.cache.evolution_ttl', 240); // Cache for 4 hours

        return Cache::remember($cacheKey, now()->addMinutes($ttl), function () use ($chainId) {
            try {
                $response = $this->client->get("evolution-chain/{$chainId}");
                return json_decode($response->getBody()->getContents(), true);
            } catch (RequestException $e) {
                Log::error('PokeAPI evolution chain request failed', [
                    'chain_id' => $chainId,
                    'status' => $e->getCode(),
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        });
    }

    /**
     * Get multiple pokemon details efficiently using concurrent requests
     * This follows the async best practices mentioned in the PokeAPI docs
     */
    public function getMultiple(array $ids): array
    {
        $results = [];

        // For now, we'll use sequential requests with caching
        // In production, consider implementing with Guzzle's Pool for concurrent requests
        foreach ($ids as $id) {
            try {
                $results[] = $this->getDetailed($id);
            } catch (RequestException $e) {
                Log::warning('Failed to fetch pokemon in batch', [
                    'id' => $id,
                    'error' => $e->getMessage()
                ]);
                // Continue with other pokemon instead of failing entirely
            }
        }

        return $results;
    }
}
