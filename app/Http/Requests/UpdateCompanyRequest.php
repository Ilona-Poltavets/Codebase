<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('companies', 'name')->ignore($this->route('company'))],
            'description' => 'nullable|string',
            'domain' => 'required|string|max:255',
            'owner_id' => 'required|exists:users,id',
            'plan' => 'required|in:free,pro,pro_enterprise',
        ];
    }
}
