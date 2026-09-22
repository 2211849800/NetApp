<?php

namespace App\Http\Requests\Admin;

class UpdateBankAccountRequest extends AdminResourceRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage_bank_accounts') ?? false;
    }

    public function rules(): array
    {
        return [
            'bank_name' => ['required', 'string', 'max:255'],
            'account_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:100'],
            'transfer_instructions' => ['nullable', 'string'],
            'status' => $this->statusRule(),
        ];
    }
}
