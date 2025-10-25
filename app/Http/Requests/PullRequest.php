<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PullRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'repository_owner' => ['nullable', 'string', 'max:255'],
            'repository' => ['nullable', 'string', 'max:255'],
            'base_branch_sha' => ['nullable', 'string', 'max:255'],
            'target_branch_sha' => ['nullable', 'string', 'max:255'],
            'base_branch' => ['nullable', 'string', 'max:255'],
            'target_branch' => ['nullable', 'string', 'max:255'],
            'ticket_key' => ['nullable', 'string', 'max:255'],
            'template_id' => ['nullable', 'integer', 'exists:templates,id'],
        ];
    }

}
