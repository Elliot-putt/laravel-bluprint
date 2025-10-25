<?php

namespace App\Services;

use App\Models\Template;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Data\TemplateData;
use Illuminate\Support\Collection;

class TemplateService
{
    /**
     * Create default templates for a user.
     *
     * @param User $user
     * @return Template
     */
    public function createDefaultTemplates(User $user): Template
    {
        // Create default template
        $template = Template::create([
            'user_id' => $user->id,
            'name' => 'Default Template',
            'title_template' => 'Write a title explaining code changes in under 15 words.',
            'body_template' => '
            Format pull request body as follows:

            ### Summary
            Brief high-level summary of changes in under 15 words.

            ### Detailed Changes
            Short paragraph explaining why and what changes do.
            List major code changes if applicable.
            Include database changes if applicable.
            Mention new dependencies or removed code if applicable.

            ### Testing Plan
            Steps to test changes (numbered 1,2,3,4)
            ',
            'is_default' => true,
        ]);

        // Set as user's default template
        $user->update(['default_template_id' => $template->id]);

        return $template;
    }

    /**
     * Store a new template.
     *
     * @param array $validated
     * @param bool $isDefault
     * @return Template
     */
    public function storeTemplate(array $validated, bool $isDefault = false): Template
    {
        $user = User::find(array_get($validated, 'user_id'));

        // If this is marked as default, unmark any other default templates
        if ($isDefault) {
            $user->templates()->where('is_default', true)->update(['is_default' => false]);
        }

        $template = Template::create($validated);

        // If this is marked as default, update the user's default_template_id
        if ($isDefault) {
            $user->update(['default_template_id' => $template->id]);
        }

        return $template;
    }

    /**
     * Update an existing template.
     *
     * @param Template $template
     * @param array $validated
     * @param bool $isDefault
     * @return Template
     */
    public function updateTemplate(Template $template, array $validated, bool $isDefault = false): Template
    {
        $user = User::find(array_get($validated, 'user_id'));

        // If this is being marked as default, unmark any other default templates
        if ($isDefault && !$template->is_default) {
            $user->templates()->where('is_default', true)->update(['is_default' => false]);
        }

        $template->update($validated);

        // Update the user's default_template_id if needed
        if ($isDefault) {
            $user->update(['default_template_id' => $template->id]);
        } elseif ($template->is_default && !$isDefault) {
            // If this was the default and is no longer, clear the user's default_template_id
            $user->update(['default_template_id' => null]);
        }

        return $template;
    }

    /**
     * Set a template as default.
     *
     * @param Template $template
     * @return Template
     */
    public function setDefaultTemplate(Template $template): Template
    {
        $user = Auth::user();

        $user->templates()->where('is_default', true)->update(['is_default' => false]);

        $template->update(['is_default' => true]);

        $user->update(['default_template_id' => $template->id]);

        return $template;
    }

    public function getUserTemplates(User $user): Collection
    {
        return TemplateData::collect($user->templates);
    }
}
