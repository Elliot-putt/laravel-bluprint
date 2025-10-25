<?php

namespace App\Services;

use App\Models\Template;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;

class AccountService
{
    public function handleCallback(): void
    {
        $gitHubUser = Socialite::driver('github')->user();

        $user = User::firstWhere('email', $gitHubUser->getEmail());

        $template = null;

        if (! $user) {
            $user = app(UserService::class)->store([
                'name' => $gitHubUser->getName() ?? $gitHubUser->getNickname(),
                'github_username' => $gitHubUser->getNickname(),
                'email' => $gitHubUser->getEmail(),
                'email_verified_at' => now(),
                'github_access_token' => $gitHubUser->token,
                'github_id' => $gitHubUser->getId(),
                'password' => $gitHubUser->getId(),
            ]);

            $template = Template::firstWhere('is_default', true)->replicate();
            $template->user_id = $user->id;
            $template->save();
        }

        $user->update([
            'github_access_token' => $gitHubUser->token,
            'default_template_id' => $template ? $template->id : $user->default_template_id,
        ]);

        auth()->login($user , true);
    }

    public function redirectToProvider(): RedirectResponse
    {
        return Socialite::driver('github')
            ->scopes(['repo', 'user', 'admin:org', 'workflow'])
            ->redirect();
    }

}
