<?php

namespace App\Services;

use App\Exceptions\AiQuotaExceededException;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class AiQuota
{
    public function consume(string $feature, ?User $user, string $ip): int
    {
        $limit = (int) config("ai.daily_limits.{$feature}", 0);
        $identity = $user ? 'user:'.$user->id : 'ip:'.sha1($ip);
        $day = now()->format('Ymd');
        $ttl = now()->endOfDay()->diffInSeconds(now());
        $key = "ai:repair:{$feature}:{$identity}:{$day}";
        $global = "ai:repair:global:{$day}";

        if ((int) Cache::get($key, 0) >= $limit || (int) Cache::get($global, 0) >= config('ai.global_daily_limit')) {
            throw new AiQuotaExceededException(max(0, $limit - (int) Cache::get($key, 0)));
        }

        Cache::add($key, 0, $ttl);
        Cache::add($global, 0, $ttl);
        Cache::increment($key);
        Cache::increment($global);

        return max(0, $limit - (int) Cache::get($key, 0));
    }

    public function cacheKey(string $feature, array|string $input): string
    {
        return 'ai:repair:result:'.$feature.':'.sha1(json_encode($input, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: (string) $input);
    }
}
