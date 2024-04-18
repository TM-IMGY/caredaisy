<?php

namespace App;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\InteractsWithTime;

trait ApiLoginLimitTrait 
{
    use InteractsWithTime;

    // 試行回数の加算
    public function hit($key, $decaySeconds = 60)
    {
        $key = $this->cleanRateLimiterKey($key);

        cache::add(
            $key.':timer', $this->availableAt($decaySeconds), $decaySeconds
        );

        $added = cache::add($key, 0, $decaySeconds);

        $hits = (int) cache::increment($key);

        if (! $added && $hits == 1) {
            cache::put($key, 1, $decaySeconds);
        }

        return $hits;
    }

    // 試行回数超過してない？
    private function tooManyAttempts($key, $maxAttempts)
    {
        $key = $this->cleanRateLimiterKey($key);

        if ($this->attempts($key) >= $maxAttempts) {
            if (cache::has($key.':timer')) {
                return true;
            }
            $this->resetAttempts($key);
        }
        return false;
    }

    // 試行回数リセットします
    public function resetAttempts($key)
    {
        $key = $this->cleanRateLimiterKey($key);
        return cache::forget($key);
    }

    // 試行回数もらえます？
    public function attempts($key)
    {
        $key = $this->cleanRateLimiterKey($key);
        return cache::get($key, 0);
    }

    // keyを掃除して返します
    public function cleanRateLimiterKey($key)
    {
        return preg_replace('/&([a-z])[a-z]+;/i', '$1', htmlentities($key));
    }
}
