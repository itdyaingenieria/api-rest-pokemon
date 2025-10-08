<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BatchFavoritesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.poke_id' => 'required|string',
            'items.*.name' => 'nullable|string|max:255',
            'items.*.image' => 'nullable|url|max:1000',
            'items.*.description' => 'nullable|string|max:2000',
        ];
    }
}
