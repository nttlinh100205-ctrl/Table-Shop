<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CheckGeminiCommand extends Command
{
    protected $signature = 'ai:check';
    protected $description = 'Check Gemini credentials with a minimal request, without sending customer data';

    public function handle(): int
    {
        $key = trim((string) config('services.gemini.api_key'));
        if (!$key) { $this->error('GEMINI_API_KEY is missing.'); return 1; }
        try {
            $modelName = preg_replace('#^models/#', '', trim((string) config('services.gemini.model', 'gemini-flash-latest'))) ?: 'gemini-flash-latest';
            $model = rawurlencode($modelName);
            $response = Http::connectTimeout(5)->timeout(30)->withHeaders(['x-goog-api-key' => $key])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                    'contents' => [['role' => 'user', 'parts' => [['text' => 'Reply with OK.']]]],
                    'generationConfig' => \App\Services\GeminiChatService::generationConfig($modelName),
                ]);
            if ($response->successful() && \App\Services\GeminiChatService::responseText($response) !== '') {
                $this->info('Gemini connection successful.'); return 0;
            }
            $this->error('Gemini HTTP '.$response->status().' ('.$response->json('error.status', 'no content').').');
            $reason = \App\Services\GeminiChatService::failureReason($response);
            $this->line(match ($reason) {
                'AI_ACCESS_DENIED' => 'Google denied API access. Check project access, API restrictions and key permissions in Google AI Studio.',
                'AI_INVALID_KEY' => 'Check GEMINI_API_KEY in the environment and rebuild the configuration cache.',
                'AI_MODEL_UNAVAILABLE' => 'GEMINI_MODEL is unavailable for this key. Choose an available text model from Google AI Studio.',
                'AI_RATE_LIMIT' => 'Check Gemini project quota and billing; repeated requests will not resolve exhausted quota.',
                default => 'Check model support and provider status. No answer text was returned.',
            });
            if (str_contains(strtolower((string)$response->json('error.message','')), 'project has been denied access')) {
                $this->error('PROJECT_ACCESS_DENIED: Google has blocked this project. Request a review from Google support; changing the model will not remove the block.');
            }
        } catch (\Throwable $e) {
            $this->error('Gemini connection failed: '.get_class($e).'. Check network and trusted CA configuration.');
        }
        return 1;
    }
}
