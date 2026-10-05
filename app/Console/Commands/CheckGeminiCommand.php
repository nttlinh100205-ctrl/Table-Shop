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
        $key = config('services.gemini.api_key');
        if (!$key) { $this->error('GEMINI_API_KEY is missing.'); return 1; }
        try {
            $model = rawurlencode(config('services.gemini.model', 'gemini-flash-latest'));
            $response = Http::connectTimeout(5)->timeout(30)->withHeaders(['x-goog-api-key' => $key])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                    'contents' => [['role' => 'user', 'parts' => [['text' => 'Reply with OK.']]]],
                    'generationConfig' => ['maxOutputTokens' => 64],
                ]);
            if ($response->successful() && $response->json('candidates.0.content.parts')) {
                $this->info('Gemini connection successful.'); return 0;
            }
            $this->error('Gemini HTTP '.$response->status().' ('.$response->json('error.status', 'no content').').');
        } catch (\Throwable $e) {
            $this->error('Gemini connection failed: '.get_class($e).'. Check network and trusted CA configuration.');
        }
        return 1;
    }
}
