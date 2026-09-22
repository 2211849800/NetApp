<?php

namespace App\Http\Requests\Admin;

class StorePackageRequest extends AdminResourceRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage_packages') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'data_allowance' => ['nullable', 'string', 'max:100'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'status' => $this->statusRule(),
        ];
    }
}
