<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Request;
use Jenssegers\Agent\Agent;

class ActivityLogService
{
    public static function log(int $userId, string $action): void
    {
        $agent = new Agent;

        ActivityLog::create([
            'user_id' => $userId,
            'action' => $action,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'device' => self::getDeviceType($agent),
            'browser' => $agent->browser(),
            'platform' => $agent->platform(),
            'location' => null, // Can be enhanced with IP geolocation service
        ]);
    }

    private static function getDeviceType(Agent $agent): string
    {
        if ($agent->isDesktop()) {
            return 'Desktop';
        } elseif ($agent->isTablet()) {
            return 'Tablet';
        } elseif ($agent->isMobile()) {
            return 'Mobile';
        }

        return 'Unknown';
    }

    public static function getRecentActivity(int $userId, int $limit = 10)
    {
        return ActivityLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
