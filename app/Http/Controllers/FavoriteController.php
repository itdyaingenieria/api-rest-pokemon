<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFavoriteRequest;
use App\Http\Requests\BatchFavoritesRequest;
use App\Repositories\FavoriteRepository;
use App\Repositories\PokeapiRepository;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    use ResponseTrait;

    protected FavoriteRepository $repo;
    protected PokeapiRepository $pokeRepo;

    public function __construct(FavoriteRepository $repo, PokeapiRepository $pokeRepo)
    {
        $this->repo = $repo;
        $this->pokeRepo = $pokeRepo;
    }

    public function index(Request $request)
    {
        $user  = $request->user();
        $items = $this->repo->forUser($user->id);
        return $this->responseSuccess($items);
    }

    public function store(StoreFavoriteRequest $request)
    {
        $user = $request->user();

        // Prevent duplicates
        $existing = $this->repo->findByUserAndPoke($user->id, $request->input('poke_id'));
        if ($existing) {
            return $this->responseError(['favorite' => 'Already exists'], 'Duplicate favorite', 409);
        }

        $fav = $this->repo->create([
            'user_id' => $user->id,
            'poke_id' => $request->input('poke_id'),
            'name'    => $request->input('name'),
            'image'   => $request->input('image'),
            'description' => $request->input('description'),
        ]);

        return $this->responseSuccess($fav, 'Favorite created', 201);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $fav = $this->repo->findByUserAndPoke($user->id, $id);
        if (! $fav) {
            return $this->responseError(['favorite' => 'Not found'], 'Favorite not found', 404);
        }

        $this->repo->delete($fav);
        return $this->responseSuccess(null, 'Favorite deleted', 200);
    }

    /**
     * Batch create favorites from a list of items (each with poke_id and optional overrides)
     */
    public function storeBatch(BatchFavoritesRequest $request)
    {
        $user = $request->user();
        $created = [];
        $skipped = [];

        foreach ($request->input('items') as $item) {
            $pokeId = $item['poke_id'];

            // skip if exists
            if ($this->repo->findByUserAndPoke($user->id, $pokeId)) {
                $skipped[] = $pokeId;
                continue;
            }

            // get details from pokeapi
            $details = $this->pokeRepo->getDetailed($pokeId);

            $name = $item['name'] ?? $details['name'] ?? $pokeId;
            $image = $item['image'] ?? $details['image'] ?? null;
            $description = $item['description'] ?? $details['description'] ?? null;

            $fav = $this->repo->create([
                'user_id' => $user->id,
                'poke_id' => (string) $pokeId,
                'name' => $name,
                'image' => $image,
                'description' => $description,
            ]);

            $created[] = $fav;
        }

        return $this->responseSuccess(['created' => $created, 'skipped' => $skipped], 'Batch processed', 201);
    }
}
