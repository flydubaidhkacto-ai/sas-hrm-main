<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = [
        'name',
        'parent_id',
        'type',
        'latitude',
        'longitude',
        'radius',
        'description',
        'is_active',
        'phone',
        'created_by'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean'
    ];
    protected $appends = [
        'full_path'
    ];

    static function boot()
    {
        parent::boot();

        static::deleting(function ($location) {
            // Prevent deletion if location has children
            if ($location->children()->exists()) {
                foreach ($location->children as $child) {
                    $child->delete(); // Recursively delete children
                }
            }
        });
    }
    /**
     * Get the parent location
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }

    /**
     * Get the child locations
     */
    public function children(): HasMany
    {
        return $this->hasMany(Location::class, 'parent_id');
    }

    /**
     * Get all active child locations
     */
    public function activeChildren(): HasMany
    {
        return $this->children()->where('is_active', true);
    }

    /**
     * Get users assigned to this location
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all descendants (recursive)
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get all ancestors
     */
    public function ancestors()
    {
        $ancestors = collect();
        $parent = $this->parent;

        while ($parent) {
            $ancestors->push($parent);
            $parent = $parent->parent;
        }

        return $ancestors->reverse();
    }

    /**
     * Get the full path (breadcrumb)
     */
    public function getFullPathAttribute()
    {
        $path = $this->ancestors()->pluck('name')->toArray();
        $path[] = $this->name;

        return implode(' > ', $path);
    }

    /**
     * Get the full hierarchy path (alias for getFullPathAttribute)
     */
    public function getFullHierarchy()
    {
        return $this->getFullPathAttribute();
    }

    /**
     * Get root locations (countries)
     */
    public static function roots()
    {
        return static::whereNull('parent_id')->where('is_active', true);
    }

    /**
     * Get locations by type
     */
    public static function byType($type)
    {
        return static::where('type', $type)->where('is_active', true);
    }

    /**
     * Check if location has coordinates
     */
    public function hasCoordinates()
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }

    /**
     * Calculate distance between two locations in meters
     */
    public function distanceTo($latitude, $longitude)
    {
        if (!$this->hasCoordinates()) {
            return null;
        }

        $earthRadius = 6371000; // Earth's radius in meters

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Check if a point is within this location's radius
     */
    public function isWithinRadius($latitude, $longitude)
    {
        if (!$this->hasCoordinates() || !$this->radius) {
            return false;
        }

        $distance = $this->distanceTo($latitude, $longitude);
        return $distance <= $this->radius;
    }

    /**
     * Scope to get locations within radius of given coordinates
     */
    public function scopeWithinRadius($query, $latitude, $longitude, $maxDistance = null)
    {
        return $query->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->selectRaw("
                        *,
                        (6371000 * acos(
                            cos(radians(?)) * cos(radians(latitude)) *
                            cos(radians(longitude) - radians(?)) +
                            sin(radians(?)) * sin(radians(latitude))
                        )) AS distance
                    ", [$latitude, $longitude, $latitude])
                    ->when($maxDistance, function($q) use ($maxDistance) {
                        return $q->having('distance', '<=', $maxDistance);
                    })
                    ->orderBy('distance');
    }
    function location_admins()
    {
        return $this->belongsToMany(User::class, 'location_user_admin');
    }
}
