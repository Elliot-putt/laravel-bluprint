<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMediaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // User must be authenticated to upload media
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
            'file' => [
                'required',
                'file',
                'max:102400', // 100MB max
                'mimes:jpg,jpeg,png,gif,bmp,svg,webp,mp4,avi,mov,wmv,flv,webm,mkv,m4v,3gp,pdf,doc,docx,txt,csv,xlsx,xls'
            ],
            'collection' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9_-]+$/' // Only alphanumeric, underscore, and dash
            ],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'A file is required for upload.',
            'file.file' => 'The uploaded item must be a valid file.',
            'file.max' => 'The file size cannot exceed 100MB.',
            'file.mimes' => 'The file must be a valid image, video, or document type.',
            'collection.string' => 'The collection name must be a string.',
            'collection.max' => 'The collection name cannot exceed 255 characters.',
            'collection.regex' => 'The collection name can only contain letters, numbers, underscores, and dashes.',
        ];
    }

    /**
     * Get the validated collection name or default.
     */
    public function getCollection(): string
    {
        return $this->validated('collection', 'default');
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'file' => 'media file',
            'collection' => 'collection name',
        ];
    }
}
