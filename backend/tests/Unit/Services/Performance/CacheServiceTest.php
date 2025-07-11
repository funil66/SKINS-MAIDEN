<?php

namespace Tests\Unit\Services\Performance;

use Tests\TestCase;
use App\Services\Performance\CacheService;
use Illuminate\Support\Facades\Cache;

class CacheServiceTest extends TestCase
{
    private CacheService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CacheService();
    }

    public function test_remember_caches_value()
    {
        $key = 'test_key';
        $value = 'test_value';
        
        $result = $this->service->remember($key, function() use ($value) {
            return $value;
        }, 5);

        $this->assertEquals($value, $result);
        $this->assertTrue(Cache::has($key));
    }

    public function test_remember_collection_returns_collection()
    {
        $key = 'test_collection';
        $data = ['item1', 'item2'];

        $result = $this->service->rememberCollection($key, function() use ($data) {
            return $data;
        }, 5);

        $this->assertIsObject($result);
        $this->assertEquals(collect($data), $result);
    }

    public function test_forget_removes_cached_item()
    {
        $key = 'test_forget';
        $value = 'test_value';

        Cache::put($key, $value, 300);
        $this->assertTrue(Cache::has($key));

        $this->service->forget($key);
        
        $this->assertFalse(Cache::has($key));
    }
}
