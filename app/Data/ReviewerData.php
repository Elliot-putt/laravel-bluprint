<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class ReviewerData extends Data
{
    public function __construct(
        public int|string $id,
        public string $name,
        public string $login,
    ) {}
} 