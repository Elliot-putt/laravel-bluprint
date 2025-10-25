<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class JiraTicketData extends Data
{
    public function __construct(
        public string $key,
        public string $summary,
        public string $status,
        public ?string $assignee,
        public ?string $priority,
        public string $created,
        public string $updated,
        public string $issueType,
    ) {}
} 