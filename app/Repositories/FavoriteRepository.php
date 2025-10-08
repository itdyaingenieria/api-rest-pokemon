<?php

namespace App\Repositories;

use App\Models\Favorite;
use Illuminate\Database\Eloquent\Collection;

class FavoriteRepository
{
    public function forUser(int $userId): Collection
    {
        return Favorite::where('user_id', $userId)->get();
    }

    public function findByUserAndPoke(int $userId, string $pokeId): ?Favorite
    {
        return Favorite::where('user_id', $userId)->where('poke_id', $pokeId)->first();
    }

    public function create(array $data): Favorite
    {
        return Favorite::create($data);
    }

    public function delete(Favorite $favorite): bool
    {
        return (bool) $favorite->delete();
    }
}
