<?php

namespace App\Http\Requests\Admin;

class UpdateOfferRequest extends AdminResourceRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage_offers') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'status' => $this->statusRule(),
        ];
    }
}
