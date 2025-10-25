<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class RecentBranchData extends Data
{
    public function __construct(
        public string $repository,
        public string $repositoryOwner,
        public string $repositoryFullName,
        public string $name,
        public string $sha,
        public string $lastCommitDate,
        public string $lastCommitMessage,
        public string $lastCommitAuthor,
        public string $branchCreationDate,
        public bool $hasOpenPr,
        public bool $isRecentlyCreated,
        public int $hoursSinceLastCommit,
        public bool $suggestedForPr,
        public bool $protected = false,
    ) {}
} 