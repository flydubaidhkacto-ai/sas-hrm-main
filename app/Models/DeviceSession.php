<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\PersonalAccessToken;

class DeviceSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_id',
        'device_name',
        'device_type',
        'platform',
        'browser',
        'ip_address',
        'user_agent',
        'session_token',
        'personal_access_token_id',
        'last_activity',
        'is_current'
    ];

    protected $casts = [
        'last_activity' => 'datetime',
        'is_current' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function personalAccessToken(): BelongsTo
    {
        return $this->belongsTo(PersonalAccessToken::class);
    }

    /**
     * Generate a unique device identifier based on user agent and other factors
     */
    public static function generateDeviceId(string $userAgent, string $ipAddress = null): string
    {
        return hash('sha256', $userAgent . ($ipAddress ?? '') . config('app.key'));
    }

    /**
     * Parse user agent to extract device information
     */
    public static function parseUserAgent(string $userAgent): array
    {
        $info = [
            'device_type' => 'unknown',
            'platform' => 'unknown',
            'browser' => 'unknown',
            'device_name' => 'Unknown Device'
        ];

        // Detect mobile devices
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            $info['device_type'] = 'mobile';

            if (strpos($userAgent, 'iPhone') !== false) {
                $info['platform'] = 'iOS';
                $info['device_name'] = 'iPhone';
            } elseif (strpos($userAgent, 'iPad') !== false) {
                $info['platform'] = 'iOS';
                $info['device_name'] = 'iPad';
                $info['device_type'] = 'tablet';
            } elseif (strpos($userAgent, 'Android') !== false) {
                $info['platform'] = 'Android';
                $info['device_name'] = 'Android Device';
                if (strpos($userAgent, 'Mobile') === false) {
                    $info['device_type'] = 'tablet';
                }
            }
        } else {
            $info['device_type'] = 'web';

            // Detect desktop platforms
            if (strpos($userAgent, 'Windows') !== false) {
                $info['platform'] = 'Windows';
                $info['device_name'] = 'Windows PC';
            } elseif (strpos($userAgent, 'Macintosh') !== false) {
                $info['platform'] = 'macOS';
                $info['device_name'] = 'Mac';
            } elseif (strpos($userAgent, 'Linux') !== false) {
                $info['platform'] = 'Linux';
                $info['device_name'] = 'Linux PC';
            }
        }

        // Detect browsers
        if (strpos($userAgent, 'Chrome') !== false && strpos($userAgent, 'Edg') === false) {
            $info['browser'] = 'Chrome';
        } elseif (strpos($userAgent, 'Firefox') !== false) {
            $info['browser'] = 'Firefox';
        } elseif (strpos($userAgent, 'Safari') !== false && strpos($userAgent, 'Chrome') === false) {
            $info['browser'] = 'Safari';
        } elseif (strpos($userAgent, 'Edg') !== false) {
            $info['browser'] = 'Microsoft Edge';
        } elseif (strpos($userAgent, 'Opera') !== false) {
            $info['browser'] = 'Opera';
        }

        return $info;
    }

    /**
     * Create or update device session
     */
    public static function createOrUpdateSession(User $user, string $userAgent, string $ipAddress = null, PersonalAccessToken $token = null, string $sessionToken = null): self
    {
        $deviceId = self::generateDeviceId($userAgent, $ipAddress);
        $deviceInfo = self::parseUserAgent($userAgent);

        // Mark all other sessions as not current for this user
        self::where('user_id', $user->id)->update(['is_current' => false]);

        return self::updateOrCreate(
            [
                'user_id' => $user->id,
                'device_id' => $deviceId,
            ],
            array_merge($deviceInfo, [
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'session_token' => $sessionToken,
                'personal_access_token_id' => $token?->id,
                'last_activity' => now(),
                'is_current' => true,
            ])
        );
    }

    /**
     * Clean up old sessions (older than specified days)
     */
    public static function cleanupOldSessions(int $days = 30): int
    {
        return self::where('last_activity', '<', now()->subDays($days))
            ->delete();
    }

    /**
     * Get active sessions for a user
     */
    public static function getActiveSessions(User $user, int $days = 30)
    {
        return self::where('user_id', $user->id)
            ->where('last_activity', '>=', now()->subDays($days))
            ->orderBy('last_activity', 'desc')
            ->get();
    }

    /**
     * Terminate a specific device session
     */
    public function terminate(): bool
    {
        // Delete associated API token if exists
        if ($this->personal_access_token_id) {
            PersonalAccessToken::find($this->personal_access_token_id)?->delete();
        }

        // For web sessions, we'll handle session invalidation in the controller

        return $this->delete();
    }
}
