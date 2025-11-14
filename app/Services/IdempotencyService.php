<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class IdempotencyService
{
    public function ensure(string $key, ?int $actorId, string $route, array $payload): bool
    {
        $hash = hash('sha256', json_encode($payload));

        return DB::transaction(function () use ($key, $actorId, $route, $hash) {
            $exists = DB::table('idempotency_keys')->where('key', $key)->exists();
            if ($exists) {
                return false;
            }

            DB::table('idempotency_keys')->insert([
                'key' => $key,
                'actor_id' => $actorId,
                'route' => $route,
                'payload_hash' => json_encode(['sha256' => $hash]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return true;
        });
    }
}
