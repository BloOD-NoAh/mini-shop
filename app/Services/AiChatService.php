<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiChatService
{
    public function provider(): string
    {
        $dbProvider = \App\Models\Setting::get('ai.provider');
        return $dbProvider ?: config('ai.default_provider', 'gemini');
    }

    public function modelFor(string $provider): string
    {
        $settingsModel = \App\Models\Setting::get('ai.model');
        if (is_array($settingsModel) && ($settingsModel[$provider] ?? null)) {
            return $settingsModel[$provider];
        }
        $cfg = config("ai.providers.$provider");
        return $cfg['default_model'] ?? '';
    }

    public function respond(array $messages, array $options = []): string
    {
        $provider = $options['provider'] ?? $this->provider();
        $model = $options['model'] ?? $this->modelFor($provider);

        return match ($provider) {
            'ollama' => $this->callOllama($model, $messages),
            'openai' => $this->callOpenAI($model, $messages),
            'anthropic' => $this->callAnthropic($model, $messages),
            default => $this->callGemini($model, $messages),
        };
    }

    protected function callOllama(string $model, array $messages): string
    {
        $base = rtrim(config('ai.providers.ollama.base_url', 'http://localhost:11434'), '/');
        if (!$base) return 'Sorry, AI is not available right now.';

        // Ollama supports system role and user/assistant messages directly
        $payload = [
            'model' => $model,
            'messages' => array_map(function ($m) {
                return [
                    'role' => $m['role'],
                    'content' => $m['content'],
                ];
            }, $messages),
            'stream' => false,
            'options' => [ 'temperature' => 0.2 ],
        ];

        $res = Http::timeout(30)->post($base.'/api/chat', $payload);
        if (!$res->ok()) {
            return $this->handleHttpError('Ollama', $res);
        }
        $text = $res->json('message.content');
        return (string) ($text ?? '');
    }

    protected function callOpenAI(string $model, array $messages): string
    {
        $apiKey = config('ai.providers.openai.api_key');
        if (!$apiKey) return 'Sorry, AI is not available right now.';

        $payload = [
            'model' => $model,
            'messages' => array_map(function ($m) {
                return [
                    'role' => $m['role'],
                    'content' => $m['content'],
                ];
            }, $messages),
            'temperature' => 0.2,
        ];

        $res = Http::withToken($apiKey)
            ->timeout(20)
            ->post('https://api.openai.com/v1/chat/completions', $payload);

        if (!$res->ok()) {
            return $this->handleHttpError('OpenAI', $res);
        }
        return (string) ($res->json('choices.0.message.content') ?? '');
    }

    protected function callAnthropic(string $model, array $messages): string
    {
        $apiKey = config('ai.providers.anthropic.api_key');
        if (!$apiKey) return 'Sorry, AI is not available right now.';

        // Split system vs messages (Anthropic requires system separate)
        $system = '';
        $chatMessages = [];
        foreach ($messages as $m) {
            if ($m['role'] === 'system') { $system .= ($system ? "\n" : '').$m['content']; continue; }
            $chatMessages[] = [ 'role' => $m['role'] === 'assistant' ? 'assistant' : 'user', 'content' => $m['content'] ];
        }

        $payload = [
            'model' => $model,
            'max_tokens' => 300,
            'temperature' => 0.2,
            'system' => $system,
            'messages' => array_map(function ($m) {
                return [
                    'role' => $m['role'],
                    'content' => [ ['type' => 'text', 'text' => $m['content']] ],
                ];
            }, $chatMessages),
        ];

        $res = Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->timeout(20)
            ->post('https://api.anthropic.com/v1/messages', $payload);

        if (!$res->ok()) {
            return $this->handleHttpError('Anthropic', $res);
        }
        $content = $res->json('content.0.text');
        return (string) ($content ?? '');
    }

    protected function callGemini(string $model, array $messages): string
    {
        $apiKey = config('ai.providers.gemini.api_key');
        if (!$apiKey) return 'Sorry, AI is not available right now.';

        // Gemini uses generateContent with contents array of role parts
        $contents = [];
        foreach ($messages as $m) {
            $role = $m['role'] === 'assistant' ? 'model' : 'user';
            // System instruction: prepend as user meta-instruction
            if ($m['role'] === 'system') {
                $contents[] = [ 'role' => 'user', 'parts' => [ ['text' => $m['content']] ] ];
                continue;
            }
            $contents[] = [ 'role' => $role, 'parts' => [ ['text' => $m['content']] ] ];
        }

        $payload = [ 'contents' => $contents, 'generationConfig' => [ 'temperature' => 0.2 ] ];
        $url = sprintf('https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s', urlencode($model), urlencode($apiKey));

        $res = Http::timeout(20)->post($url, $payload);
        if (!$res->ok()) {
            return $this->handleHttpError('Gemini', $res);
        }
        $text = $res->json('candidates.0.content.parts.0.text');
        return (string) ($text ?? '');
    }

    protected function handleHttpError(string $provider, $res): string
    {
        try {
            Log::warning($provider.' API error', [
                'status' => $res->status(),
                'body' => $res->body(),
            ]);
        } catch (\Throwable $e) {
            // swallow logging errors
        }

        $status = (int) $res->status();
        if ($status === 401 || $status === 403) {
            return 'Sorry, the AI provider authentication failed. Please try again later.';
        }
        if ($status === 429) {
            return 'Sorry, the AI provider limit was reached. Please try again later.';
        }
        if ($status >= 500) {
            return 'Sorry, the AI provider is temporarily unavailable. Please try again later.';
        }
        return 'Sorry, I could not get a reply right now.';
    }
}
