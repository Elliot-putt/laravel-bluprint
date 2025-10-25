<?php

namespace Database\Seeders;

use App\Models\Template;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();

        foreach ($users as $user) {
            // Create default template for each user
            $template = Template::create([
                'user_id' => $user->id,
                'name' => 'Default Template',
                'title_template' => 'Write a title explaining code changes in under 15 words.',
                'body_template' => "
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
                ",
                'is_default' => true,
            ]);

            // Set as user's default template
            $user->update(['default_template_id' => $template->id]);
        }
    }
}
