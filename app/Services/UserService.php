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
            'password' => bcrypt($data['password']),
        ]);
    }
}
