<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    public function store(array $data): ?User
    {
        return User::create([
            'name' => array_get($data, 'name'),
            'email' => array_get($data, 'email'),
            'email_verified_at' => array_get($data, 'email_verified_at'),
            'github_username' => array_get($data, 'github_username'),
            'github_access_token' => array_get($data, 'github_access_token'),
            'github_id' => array_get($data, 'github_id'),
            'password' => bcrypt($data['password']),
            'jira_connected_email' => null,
            'jira_token' => null,
            'jira_domain' => null,
        ]);
    }
}
