<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rules\File;

class StoreProductRequest extends AdminResourceRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage_products') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_available' => ['sometimes', 'boolean'],
            'status' => $this->statusRule(),
            'image' => ['nullable', File::types(['jpg', 'jpeg', 'png', 'webp'])->max(2048)],
        ];
    }
}
