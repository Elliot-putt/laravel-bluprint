<?php

namespace App\Services;

use App\Models\GeminiChat;
use App\Models\User;
use Http;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Log;

class AIService
{
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';
    protected string $statusUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-lite';

    private const MAX_CHUNK_SIZE = 30000;
    private const MAX_TOTAL_SIZE = 150000;

    protected string $combinedRole = 'You are a pull request Generator. Generate a pull request title and body based on the code diff. Use JIRA context when available. CRITICAL: You must follow this exact format with no deviations:

TITLE_START
[title text here - no extra formatting]
TITLE_END

BODY_START
[body text here - use GitHub markdown]
BODY_END

REQUIREMENTS:
- Start response immediately with TITLE_START
- No text before TITLE_START or after BODY_END
- No code blocks around the markers
- No extra spacing or formatting around markers
- Markers must be on their own lines exactly as shown';

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

    public function generatePR(User $user, string $codeDiff, string $issueContext = '', ?int $templateId = null): array
    {
        $chunks = $this->createSmartChunks($codeDiff);

        if (empty($chunks)) {
            return [
                'title' => 'Unable to process diff - no valid chunks created.',
                'body' => 'The diff was too complex to process in chunks.'
            ];
        }

        $chat = new GeminiChat($this->apiKey);

        $initMessage = "I'm going to send you a git diff in " . count($chunks) . ' chunk' . (count($chunks) > 1 ? 's' : '') . ' for pull request analysis. ' .
            "Please analyze each chunk as I send it. After all chunks are sent, I'll ask you to generate " .
            'the final pull request title and body.' .
            $this->getContextAppend($issueContext) .
            "\n\nDo not generate the PR yet - just acknowledge you're ready to receive chunks.";

        try {
            $response = $chat->sendMessage($initMessage);

            foreach ($chunks as $index => $chunk) {
                $chunkNumber = $index + 1;
                $isLast = $chunkNumber === count($chunks);

                $chunkMessage = "Chunk {$chunkNumber} of " . count($chunks) . ":\n\n" . $chunk;

                if (!$isLast) {
                    $chunkMessage .= "\n\nPlease acknowledge this chunk and note the key changes. More chunks coming.";
                } else {
                    $chunkMessage .= "\n\nThis is the final chunk. Please acknowledge and prepare for PR generation.";
                }

                $chunkResponse = $chat->sendMessage($chunkMessage);
            }

            $finalPrompt = $this->combinedRole .
                $this->getCombinedTemplate($user, $templateId) .
                "\n\nNow please generate the pull request title and body based on all the chunks you've analyzed. " .
                'Follow the exact format specified above.';

            $finalResponse = $chat->sendMessage($finalPrompt);

            Log::info('AIService: PR generation completed', [
                'chunk_count' => count($chunks),
                'conversation_length' => $chat->getHistoryLength(),
                'final_response_length' => strlen($finalResponse),
            ]);

            return $this->parsePRResponse($finalResponse);

        } catch (\Exception $e) {
            Log::error('AIService: PR generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'title' => 'Unable to generate pull request title from chunks.',
                'body' => 'Unable to generate pull request body from chunks. Error: ' . $e->getMessage()
            ];
        }
    }

    private function createSmartChunks(string $codeDiff): array
    {
        if (strlen($codeDiff) > self::MAX_TOTAL_SIZE) {
            $codeDiff = $this->preprocessLargeDiff($codeDiff);
        }

        $lines = explode("\n", $codeDiff);
        $chunks = [];
        $currentChunk = '';
        $currentFile = '';

        foreach ($lines as $line) {
            if (preg_match('/^diff --git/', $line)) {
                if (!empty($currentChunk) && strlen($currentChunk) > 1000) {
                    $chunks[] = $this->wrapChunk($currentChunk, $currentFile);
                    $currentChunk = '';
                }
                $currentFile = $this->extractFilename($line);
            }

            if (strlen($currentChunk . $line . "\n") > self::MAX_CHUNK_SIZE) {
                if (!empty($currentChunk)) {
                    $chunks[] = $this->wrapChunk($currentChunk, $currentFile);
                    $currentChunk = '';
                }
            }

            $currentChunk .= $line . "\n";
        }

        if (!empty($currentChunk)) {
            $chunks[] = $this->wrapChunk($currentChunk, $currentFile);
        }

        return array_filter($chunks);
    }

    private function preprocessLargeDiff(string $codeDiff): string
    {
        $lines = explode("\n", $codeDiff);
        $filteredLines = [];
        $inSnapshot = false;
        $inLargeFile = false;
        $fileLineCount = 0;

        foreach ($lines as $line) {
            if (preg_match('/^diff --git/', $line)) {
                $inSnapshot = false;
                $inLargeFile = false;
                $fileLineCount = 0;
            }

            $fileLineCount++;

            if ($fileLineCount > 500) {
                $inLargeFile = true;
            }

            if (!$inSnapshot && !$inLargeFile && strlen($line) < 500) {
                $filteredLines[] = $line;
            }
        }

        return implode("\n", $filteredLines);
    }

    private function extractFilename(string $diffLine): string
    {
        if (preg_match('/^diff --git a\/(.+) b\/(.+)/', $diffLine, $matches)) {
            return $matches[1];
        }
        return 'unknown file';
    }

    private function wrapChunk(string $chunk, string $filename): string
    {
        return "File: {$filename}\n" .
            "Changes:\n" .
            "--------\n" .
            trim($chunk) . "\n\n";
    }

    private function parsePRResponse(string $response): array
    {
        $title = 'Unable to fetch from response pull request title.';
        $body = 'Unable to fetch from response pull request body.';

        if (preg_match('/TITLE_START\s*(.*?)\s*TITLE_END/s', $response, $titleMatches)) {
            $title = trim($titleMatches[1]);
        }

        if (preg_match('/BODY_START\s*(.*?)\s*BODY_END/s', $response, $bodyMatches)) {
            $bodyContent = trim($bodyMatches[1]);
            $body = trim(preg_replace('/^```(?:markdown)?\s*|```$/m', '', $bodyContent));
        }

        return [
            'title' => $title,
            'body' => $body
        ];
    }

    private function getCombinedTemplate(User $user, ?int $templateId = null): string
    {
        $titleTemplate = $this->getTitleTemplate($user, $templateId);
        $bodyTemplate = $this->getBodyTemplate($user, $templateId);

        return "\n\nTitle instructions: " . $titleTemplate .
            "\n\nBody instructions: " . $bodyTemplate;
    }

    private function getTitleTemplate(User $user, ?int $templateId = null): string
    {
        if ($templateId) {
            $template = $user->templates()->find($templateId);
            if ($template) {
                return $template->title_template;
            }
        }

        if ($user->defaultTemplate) {
            return $user->defaultTemplate->title_template;
        }

        return 'Write a title explaining code changes in under 15 words.';
    }

    private function getBodyTemplate(User $user, ?int $templateId = null): string
    {
        if ($templateId) {
            $template = $user->templates()->find($templateId);
            if ($template) {
                return $template->body_template;
            }
        }

        if ($user->defaultTemplate) {
            return $user->defaultTemplate->body_template;
        }

        return '
        Format pull request body as follows:

        ### Summary
        Brief high-level summary of changes in under 15 words.

        ### Detailed Changes
        Short paragraph explaining why and what changes do.
        List major code changes if applicable.
        Include database changes if applicable.
        Mention new dependencies or removed code if applicable.

        ### Testing Plan
        Steps to test changes (numbered 1,2,3,4)
        ';
    }

    private function getContextAppend(string $issueContext): string
    {
        return empty($issueContext) ? '' : "\n\nJIRA context to understand changes: {$issueContext}";
    }
}
