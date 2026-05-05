<?php

namespace App\Services\AI;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class QwenClient
{
    public function healthCheck(): array
    {
        if (! config('ai.enabled')) {
            return [
                'ok' => false,
                'message' => 'AI integration is disabled.',
                'status' => null,
            ];
        }

        try {
            $response = $this->request()->get(config('ai.health_endpoint'));

            return [
                'ok' => $response->successful(),
                'message' => $response->successful() ? 'Qwen endpoint responded successfully.' : 'Qwen endpoint returned an error response.',
                'status' => $response->status(),
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'message' => $e->getMessage(),
                'status' => null,
            ];
        }
    }

    public function complete(array $messages, array $options = []): array
    {
        if (! config('ai.enabled')) {
            throw new RuntimeException('AI integration is disabled.');
        }

        $response = $this->request()->post(config('ai.endpoint'), array_filter([
            'model' => $options['model'] ?? config('ai.model'),
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.4,
            'max_tokens' => $options['max_tokens'] ?? 1200,
        ], static fn ($value) => $value !== null));

        $response->throw();

        $payload = $response->json();
        $content = Arr::get($payload, 'choices.0.message.content', '');

        return [
            'raw' => $payload,
            'content' => is_array($content) ? json_encode($content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : (string) $content,
            'usage' => Arr::get($payload, 'usage', []),
            'model' => Arr::get($payload, 'model', $options['model'] ?? config('ai.model')),
        ];
    }

    private function request(): PendingRequest
    {
        $request = Http::baseUrl(config('ai.base_url'))
            ->acceptJson()
            ->asJson()
            ->timeout(config('ai.timeout'))
            ->connectTimeout(config('ai.connect_timeout'));

        if ($apiKey = config('ai.api_key')) {
            $request = $request->withToken($apiKey);
        }

        return $request;
    }
}
