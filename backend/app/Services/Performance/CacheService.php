<?php

namespace App\Services\Performance;

use Cache;
use Illuminate\Support\Collection;

class CacheService
{
    /**
     * Get cached item or store the default value.
     *
     * @param string $key
     * @param \Closure $callback
     * @param int $minutes
     * @return mixed
     */
    public function remember(string $key, \Closure $callback, int $minutes = 60)
    {
        return Cache::remember($key, $minutes * 60, $callback);
    }

    /**
     * Cache multiple items as a collection.
     *
     * @param string $key
     * @param \Closure $callback
     * @param int $minutes
     * @return Collection
     */
    public function rememberCollection(string $key, \Closure $callback, int $minutes = 60): Collection
    {
        return collect($this->remember($key, $callback, $minutes));
    }

    /**
     * Clear cache by key or pattern.
     *
     * @param string $key
     * @return bool
     */
    public function forget(string $key): bool
    {
        return Cache::forget($key);
    }
}
