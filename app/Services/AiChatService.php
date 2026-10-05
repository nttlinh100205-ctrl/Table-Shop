<?php
namespace App\Services;

class AiChatService extends GeminiChatService
{
    public static function chat(string $message, ?array $behavior = null, array $history = []): string
    {
        return match (config('services.ai.provider', 'groq')) {
            'groq' => GroqChatService::chat($message, $behavior, $history),
            'gemini' => parent::chat($message, $behavior, $history),
            default => throw new \App\Exceptions\AiUnavailableException('AI_MODEL_UNAVAILABLE'),
        };
    }
}
