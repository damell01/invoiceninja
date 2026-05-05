<?php

namespace Tests\Unit\Contracts;

use App\Services\AI\QwenClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class QwenClientTest extends TestCase
{
    public function test_it_calls_an_openai_style_qwen_endpoint(): void
    {
        config([
            'ai.enabled' => true,
            'ai.base_url' => 'http://qwen.local',
            'ai.endpoint' => '/v1/chat/completions',
            'ai.model' => 'qwen-test',
        ]);

        Http::fake([
            'http://qwen.local/v1/chat/completions' => Http::response([
                'model' => 'qwen-test',
                'choices' => [
                    [
                        'message' => [
                            'content' => 'Generated response',
                        ],
                    ],
                ],
                'usage' => [
                    'prompt_tokens' => 12,
                    'completion_tokens' => 7,
                ],
            ], 200),
        ]);

        $result = app(QwenClient::class)->complete([
            ['role' => 'user', 'content' => 'Hello'],
        ]);

        $this->assertSame('Generated response', $result['content']);
        $this->assertSame('qwen-test', $result['model']);
        Http::assertSentCount(1);
    }
}
