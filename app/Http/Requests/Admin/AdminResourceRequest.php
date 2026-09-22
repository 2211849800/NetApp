<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class AdminResourceRequest extends FormRequest
{
    protected function statusRule(): array
    {
        return ['sometimes', Rule::in(['active', 'inactive'])];
    }
}
