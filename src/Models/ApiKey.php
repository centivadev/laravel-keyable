<?php

namespace Givebutter\LaravelKeyable\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    use SoftDeletes;

    public ?string $plainTextApiKey = null;

    protected $table = 'api_keys';

    protected $fillable = [
        'key',
        'keyable_id',
        'keyable_type',
        'name',
        'last_used_at',
        'expires_at',
        'last_key_chars',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function (ApiKey $apiKey) {
            if (is_null($apiKey->key)) {
                $apiKey->plainTextApiKey = self::generate();
                $apiKey->key = hash('sha256', $apiKey->plainTextApiKey);
                $apiKey->last_key_chars = substr($apiKey->plainTextApiKey, -5);
            }
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function keyable()
    {
        return $this->morphTo();
    }

    /**
     * Generate a secure unique API key.
     *
     * @return string
     */
    public static function generate()
    {
        do {
            $key = Str::random(45);
        } while (self::keyExists($key));

        return $key;
    }

    /**
     * Get ApiKey record by key value.
     *
     * @param string $key
     *
     * @return bool
     */
    public static function getByKey($key)
    {
        return self::ofKey($key)
            ->validKey()
            ->first();
    }

    /**
     * Check if a key already exists.
     *
     * Includes soft deleted records
     *
     * @param string $key
     *
     * @return bool
     */
    public static function keyExists($key)
    {
        return self::ofKey($key)
            ->withTrashed()
            ->first() instanceof self;
    }

    /**
     * Mark key as used.
     */
    public function markAsUsed()
    {
        return $this->forceFill([
            'last_used_at' => $this->freshTimestamp()
        ])->save();
    }

    public function scopeOfKey(Builder $query, string $key): Builder
    {
        $compatibilityMode = config('keyable.compatibility_mode', false);

        if ($compatibilityMode) {
            return $query->where(function (Builder $query) use ($key) {
                if (! str_contains($key, '|')) {
                    return $query->where('key', $key)
                        ->orWhere('key', hash('sha256', $key));
                }

                [$id, $key] = explode('|', $key, 2);

                return $query
                    ->where(function (Builder $query) use ($key, $id) {
                        return $query->where('key', $key)
                            ->where('id', $id);
                    })
                    ->orWhere(function (Builder $query) use ($key, $id) {
                        return $query->where('key', hash('sha256', $key))
                            ->where('id', $id);
                    });
            });
        }

        if (! str_contains($key, '|')) {
            return $query->where('key', hash('sha256', $key));
        }

        [$id, $key] = explode('|', $key, 2);

        return $query->where('id', $id)
            ->where('key', hash('sha256', $key));
    }

    public function scopeValidKey(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            $query->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
        });
    }
}
