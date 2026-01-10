<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Track extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'spotify_id',
        'name',
        'artists',
        'album',
        'album_art_url',
        'duration_ms',
        'spotify_url',
        'preview_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'artists' => 'array',
        ];
    }

    /**
     * Get the user that owns the track.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related tracks.
     */
    public function relatedTracks(): BelongsToMany
    {
        return $this->belongsToMany(
            Track::class,
            'related_tracks',
            'track_id',
            'related_track_id'
        )->withTimestamps();
    }

    /**
     * Get tracks that have this track as a related track.
     */
    public function relatedTo(): BelongsToMany
    {
        return $this->belongsToMany(
            Track::class,
            'related_tracks',
            'related_track_id',
            'track_id'
        )->withTimestamps();
    }
}
