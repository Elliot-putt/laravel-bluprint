<?php

namespace App\Http\Requests\Settings;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'jira_token' => ['nullable', 'string'],
            'jira_domain' => ['nullable', 'string', 'regex:/^[a-zA-Z0-9-]+\.atlassian\.net$/'],
            'jira_connected_email' => ['nullable', 'string', 'email', 'max:255'],
        ];
    }
}
