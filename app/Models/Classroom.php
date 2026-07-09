<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'location',
        'room_number',
        'floor',
        'address',
        'arrival_instructions',
        'capacity',
        'hourly_rate',
        'description',
        'image_url',
        'gallery',
        'amenities',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'gallery'   => 'array',
            'amenities' => 'array',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function getHeroImageAttribute(): string
    {
        return self::publicImageUrl($this->image_url)
            ?? 'https://images.unsplash.com/photo-1517409239398-9b39ca5b40f6?auto=format&fit=crop&w=1600&q=70';
    }

    public function getGalleryImagesAttribute(): array
    {
        return collect($this->gallery ?? [])
            ->map(fn (mixed $image): ?string => self::publicImageUrl(is_string($image) ? $image : null))
            ->filter()
            ->values()
            ->all();
    }

    public static function publicImageUrl(?string $path): ?string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//')) {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            return $path;
        }

        if (str_starts_with($path, 'storage/')) {
            return '/'.$path;
        }

        return '/storage/'.ltrim($path, '/');
    }

    public function getMapEmbedUrlAttribute(): ?string
    {
        if (! $this->address) {
            return null;
        }

        return 'https://www.google.com/maps?q='.urlencode($this->address).'&output=embed';
    }
}
