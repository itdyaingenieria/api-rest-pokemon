<?php

namespace App\Http\Controllers;

use App\Repositories\PokeapiRepository;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\RequestException;

class PokemonController extends Controller
{
    use ResponseTrait;

    protected PokeapiRepository $repo;

    public function __construct(PokeapiRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * List Pokémon (proxied from PokeAPI)
     * Supports pagination and search following PokeAPI best practices
     */
    public function index(Request $request)
    {
        $limit = min((int) $request->query('limit', 20), 100); // Enforce PokeAPI max limit
        $offset = max((int) $request->query('offset', 0), 0);
        $search = $request->query('search');

        try {
            if ($search) {
                $data = $this->repo->searchByName($search, $limit);
            } else {
                $data = $this->repo->list($limit, $offset);
            }

            return $this->responseSuccess($data, 'Pokemon retrieved successfully');
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 500;
            return $this->responseError(
                ['exception' => $e->getMessage()],
                'Failed to fetch pokemon from PokeAPI',
                $statusCode
            );
        } catch (\Exception $e) {
            return $this->responseError(
                ['exception' => $e->getMessage()],
                'Failed to fetch pokemon',
                500
            );
        }
    }

    /**
     * Show a single Pokémon by id or name
     * Returns detailed pokemon data optimized for frontend consumption
     */
    public function show($id)
    {
        try {
            $data = $this->repo->getDetailed($id);
            return $this->responseSuccess($data, 'Pokemon details retrieved successfully');
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 500;

            if ($statusCode === 404) {
                return $this->responseError(
                    ['id' => $id],
                    'Pokemon not found',
                    404
                );
            }

            return $this->responseError(
                ['exception' => $e->getMessage()],
                'Failed to fetch pokemon from PokeAPI',
                $statusCode
            );
        } catch (\Exception $e) {
            return $this->responseError(
                ['exception' => $e->getMessage()],
                'Failed to fetch pokemon',
                500
            );
        }
    }

    /**
     * Get pokemon by type
     */
    public function byType($type)
    {
        try {
            $data = $this->repo->getByType($type);

            // Extract just the pokemon list from the type response
            $pokemon = [];
            foreach ($data['pokemon'] ?? [] as $pokemonData) {
                $pokemon[] = $pokemonData['pokemon'];
            }

            return $this->responseSuccess([
                'type' => $data['name'] ?? $type,
                'pokemon' => $pokemon,
                'count' => count($pokemon)
            ], 'Pokemon by type retrieved successfully');
        } catch (RequestException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 500;

            if ($statusCode === 404) {
                return $this->responseError(
                    ['type' => $type],
                    'Pokemon type not found',
                    404
                );
            }

            return $this->responseError(
                ['exception' => $e->getMessage()],
                'Failed to fetch pokemon by type',
                $statusCode
            );
        } catch (\Exception $e) {
            return $this->responseError(
                ['exception' => $e->getMessage()],
                'Failed to fetch pokemon by type',
                500
            );
        }
    }
}
