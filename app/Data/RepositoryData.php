<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class RepositoryData extends Data
{
    public function __construct(
        public int|string $id,
        public string $name,
        public string $owner,
        public string $fullName,
        public ?string $description = null,
        public ?string $link = null,
        public ?string $defaultBranch = null,
    ) {}
} 