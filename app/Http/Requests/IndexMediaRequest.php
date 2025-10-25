<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexMediaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // User must be authenticated to view their media
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'per_page' => [
                'sometimes',
                'integer',
                'min:1',
                'max:100' // Reasonable limit to prevent performance issues
            ],
            'page' => [
                'sometimes',
                'integer',
                'min:1'
            ],
            'collection' => [
                'sometimes',
                'string',
                'max:255'
            ],
            'search' => [
                'sometimes',
                'string',
                'max:255'
            ]
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'per_page.integer' => 'Items per page must be a number.',
            'per_page.min' => 'Items per page must be at least 1.',
            'per_page.max' => 'Items per page cannot exceed 100.',
            'page.integer' => 'Page number must be a number.',
            'page.min' => 'Page number must be at least 1.',
            'collection.string' => 'Collection filter must be a string.',
            'collection.max' => 'Collection filter cannot exceed 255 characters.',
            'search.string' => 'Search term must be a string.',
            'search.max' => 'Search term cannot exceed 255 characters.',
        ];
    }

    /**
     * Get the validated per_page value or default.
     */
    public function getPerPage(): int
    {
        return $this->validated('per_page', 12);
    }

    /**
     * Get the validated page value or default.
     */
    public function getPage(): int
    {
        return $this->validated('page', 1);
    }

    /**
     * Get the validated collection filter if provided.
     */
    public function getCollection(): ?string
    {
        return $this->validated('collection');
    }

    /**
     * Get the validated search term if provided.
     */
    public function getSearch(): ?string
    {
        return $this->validated('search');
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'per_page' => 'items per page',
            'page' => 'page number',
            'collection' => 'collection filter',
            'search' => 'search term',
        ];
    }
}
