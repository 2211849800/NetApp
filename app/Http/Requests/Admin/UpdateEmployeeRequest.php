<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage_employees') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $employee = $this->route('employee');
        $this->merge([
            'status' => $this->input('status', $employee?->status?->value ?? 'active'),
        ]);
    }

    public function rules(): array
    {
        $employee = $this->route('employee');

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required', 'string', 'min:3', 'max:50', 'alpha_dash:ascii',
                Rule::unique('users', 'username')->ignore($employee?->id),
            ],
            'phone' => ['required', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($employee?->id)],
            'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($employee?->id)],
            'role' => ['required', Rule::in(['employee', 'supervisor', 'admin'])],
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('role') === 'admin' && ! $this->user()?->hasPermission('manage_admins')) {
                $validator->errors()->add('role', 'ليس لديك صلاحية تعيين دور مدير.');
            }
        });
    }
}
