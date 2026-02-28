<?php

// app/Support/DriverEventLogger.php
namespace App\Support;

use App\Models\DriverEvent;
use Illuminate\Support\Facades\Auth;

class DriverEventLogger
{
    public static function log(
        string $channel,
        string $event,
        ?string $name = null,
        array $meta = [],
        ?int $tripId = null,
        ?int $statusCode = null,
        ?int $durationMs = null
    ): void {
        $user = Auth::guard('driver')->user();

        $driverId = optional($user?->driver)->id;
        $userId   = optional($user)->id;

        // 🔒 редактируем чувствительные поля
        $meta = self::sanitize($meta);

        // 📦 ограничиваем размер меты
        $metaJson = json_encode($meta);
        if ($metaJson && strlen($metaJson) > 8000) {
            $meta = ['_truncated' => true];
        }

        DriverEvent::create([
            'driver_id' => $driverId,
            'user_id' => $userId,
            'trip_id' => $tripId,
            'channel' => $channel,
            'event' => $event,
            'name' => $name,
            'path' => request()->path(),
            'method' => request()->method(),
            'status_code' => $statusCode,
            'duration_ms' => $durationMs,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'meta' => $meta ?: null,
        ]);
    }

    private static function sanitize(array $meta): array
    {
        $blacklist = [
            'password','password_confirmation','token','_token',
            'authorization','cookie','cookies','file','files','photo','image',
        ];

        array_walk_recursive($meta, function (&$value, $key) use ($blacklist) {
            if (in_array(strtolower((string)$key), $blacklist, true)) {
                $value = '[REDACTED]';
            }
            if (is_string($value) && strlen($value) > 2000) {
                $value = substr($value, 0, 2000) . '...[CUT]';
            }
        });

        return $meta;
    }
}
