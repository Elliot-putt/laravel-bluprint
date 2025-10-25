<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class BranchData extends Data
{
    public function __construct(
        public string $name,
        public string $sha,
        public string $repository,
        public string $repositoryOwner,
        public string $repositoryFullName,
        public bool $protected = false,
    ) {
    }

}
