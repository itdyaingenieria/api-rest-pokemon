<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFavoriteRequest extends FormRequest
{
    public function authorize(): bool
    {
        // User must be authenticated; middleware will handle but double-check here
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'poke_id'     => 'required|string',
            'name'        => 'required|string|max:255',
            'image'       => 'nullable|url|max:1000',
            'description' => 'nullable|string|max:2000',
        ];
    }
}
