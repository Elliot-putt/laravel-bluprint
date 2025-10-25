<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class TemplateData extends Data
{
    public function __construct(
        public int $id,
        public int $userId,
        public string $name,
        public string $titleTemplate,
        public string $bodyTemplate,
        public bool $isDefault,
        public ?array $defaultLabels = null,
    ) {}
} 