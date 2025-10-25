<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class LabelData extends Data
{
    public function __construct(
        public int|string $id,
        public string $name,
        public string $color,
    ) {}
} 