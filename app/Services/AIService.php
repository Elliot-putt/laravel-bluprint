<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class AIService
{
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';
    protected string $statusUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-lite';
    protected string $combinedRole = '';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
    }

    public function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)->withQueryParameters(['key' => $this->apiKey]);
    }

    public function isAvailable(): bool
    {
        $response = Http::withQueryParameters(['key' => $this->apiKey])
            ->get($this->statusUrl);

        if (!$response->successful()) {
            return false;
        }

        return true;
    }
}
