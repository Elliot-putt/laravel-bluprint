<?php

namespace App\Models;

use Exception;
use Illuminate\Support\Facades\Http;

class GeminiChat
{
    private string $apiKey;
    private array $history = [];

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function sendMessage(string $message): string
    {
        $this->addUserMessage($message);
        $response = $this->makeRequest();
        $this->addModelResponse($response);

        return $response;
    }

    public function getHistoryLength(): int
    {
        return count($this->history);
    }

    private function addUserMessage(string $message): void
    {
        $this->history[] = ['role' => 'user', 'parts' => [['text' => $message]]];
    }

    private function addModelResponse(string $response): void
    {
        $this->history[] = ['role' => 'model', 'parts' => [['text' => $response]]];
    }

    private function makeRequest(): string
    {
        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$this->apiKey}", [
                'contents' => $this->history,
                'generationConfig' => ['temperature' => 0.7, 'maxOutputTokens' => 2048]
            ]);

        if (!$response->successful()) {
            throw new Exception('Gemini API request failed: ' . $response->body());
        }

        $responseData = $response->json();

        if (!isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
            throw new Exception('Invalid response format from Gemini API');
        }

        return $responseData['candidates'][0]['content']['parts'][0]['text'];
    }
}
