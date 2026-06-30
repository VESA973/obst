<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    protected function validAntiBotPayload(string $key): array
    {
        $token = 'test-'.Str::random(16);

        $this->withSession([
            sprintf('antibot.%s.%s', $key, $token) => [
                'answer' => '4',
                'created_at' => now()->subSeconds(5)->timestamp,
            ],
        ]);

        return [
            'website' => '',
            'form_started_at' => now()->subSeconds(5)->timestamp,
            'antibot_token' => $token,
            'antibot_answer' => '4',
        ];
    }
}
