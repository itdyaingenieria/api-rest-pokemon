<?php

return [
    // Base URL for PokeAPI - following official documentation
    'base_url' => env('POKEAPI_BASE_URL', 'https://pokeapi.co/api/v2'),

    // Cache TTL in minutes - aggressive caching following PokeAPI best practices
    // PokeAPI has no official rate limits but recommends caching to reduce hosting costs
    'cache' => [
        'list_ttl' => env('POKEAPI_CACHE_LIST_TTL', 60),      // 1 hour for pokemon lists
        'detail_ttl' => env('POKEAPI_CACHE_DETAIL_TTL', 120), // 2 hours for pokemon details
        'type_ttl' => env('POKEAPI_CACHE_TYPE_TTL', 240),     // 4 hours for type data
        'evolution_ttl' => env('POKEAPI_CACHE_EVOLUTION_TTL', 240), // 4 hours for evolution chains
    ],

    // Request settings following PokeAPI documentation
    'requests' => [
        'timeout' => env('POKEAPI_TIMEOUT', 10),              // 10 second timeout
        'max_limit' => env('POKEAPI_MAX_LIMIT', 100),         // Max 100 items per request
        'default_limit' => env('POKEAPI_DEFAULT_LIMIT', 20),  // Default 20 items per request
        'user_agent' => env('POKEAPI_USER_AGENT', 'Laravel-PokeAPI-Client/1.0'),
    ],

    // Search settings
    'search' => [
        'max_search_results' => env('POKEAPI_SEARCH_MAX_RESULTS', 1000), // Max pokemon to search through
    ],
];
