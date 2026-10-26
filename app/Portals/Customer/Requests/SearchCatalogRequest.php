<?php

namespace App\Portals\Customer\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchCatalogRequest extends FormRequest
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
            'keyword' => 'nullable|string|max:120',
            'category_id' => 'nullable',
            'tenant_id' => 'nullable',
            'location' => 'nullable|string|max:100',
            'start_date' => 'nullable|string',
            'end_date' => 'nullable|string',
            'type' => 'nullable|string|in:camera,studio,all',
        ];
    }
}
