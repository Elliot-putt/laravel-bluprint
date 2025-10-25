<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTemplateRequest extends FormRequest
{
    private const MAX_TITLE_TEMPLATE_LENGTH = 5000;
    private const MAX_BODY_TEMPLATE_LENGTH = 75000;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'title_template' => ['required', 'string', 'max:' . self::MAX_TITLE_TEMPLATE_LENGTH],
            'body_template' => ['required', 'string', 'max:' . self::MAX_BODY_TEMPLATE_LENGTH],
            'is_default' => ['boolean'],
            'default_labels' => ['nullable', 'array'],
            'default_labels.*' => ['string', 'max:255'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

}
