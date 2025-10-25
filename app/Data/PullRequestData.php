<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class PullRequestData extends Data
{
    public function __construct(
        public int $number,
        public string $title,
        public string $body,
        public string $state,
        public string $createdAt,
        public string $updatedAt,
        public ?string $mergedAt,
        public ?string $closedAt,
        public bool $isDraft,
        public string $mergeable,
        public string $url,
        public string $repositoryName,
        public string $repositoryOwner,
        public string $repositoryFullName,
        public array $user, // ['login' => string]
        public array $head, // ['ref' => string, 'sha' => string]
        public array $base, // ['ref' => string, 'sha' => string]
        public array $labels, // [{name, color}]
        public array $assignees, // [string]
        public bool $isMine,
        public ?string $currentBaseSha,
        public ?string $prBaseSha,
        public bool $isBehind,
        public bool $isUpToDate,
        public ?int $comments = null,
        public ?int $commits = null,
        public ?int $changedFiles = null,
        public ?int $additions = null,
        public ?int $deletions = null,
        public bool $isMerged = false,
    ) {}
} 