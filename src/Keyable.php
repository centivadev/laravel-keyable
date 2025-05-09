<?php

namespace Givebutter\LaravelKeyable;

use Givebutter\LaravelKeyable\Models\ApiKey;
use Illuminate\Database\Eloquent\Model;

trait Keyable
{
    public function apiKeys()
    {
        return $this->morphMany(ApiKey::class, 'keyable');
    }

    public function createApiKey(array $attributes = []): NewApiKey
    {
        $planTextApiKey = ApiKey::generate();
        if (isset($attributes['expires_at'])) {
            $dateTime = \DateTime::createFromFormat('Y-m-d H:i', $attributes['expires_at']) ?: \DateTime::createFromFormat('Y-m-d', $attributes['expires_at']);

            $attributes['expires_at'] = (
                $dateTime
                    ? $dateTime->format('Y-m-d H:i:s')
                    : null
            );
        }

        $apiKey = Model::withoutEvents(function () use ($planTextApiKey, $attributes) {
            return $this->apiKeys()->create([
                'key' => hash('sha256', $planTextApiKey),
                'name' => $attributes['name'] ?? null,
                'expires_at' => $attributes['expires_at'],
            ]);
        });

        return new NewApiKey($apiKey, "{$apiKey->getKey()}|{$planTextApiKey}|ExpiredAt:{$apiKey->expires_at}");
    }
}
