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
        'album_id',
        'spotify_id',
        'name',
        'artists',
        'album',
        'album_art_url',
        'duration_ms',
        'spotify_url',
        'preview_url',
        'bpm',
        'camelot',
        'energy',
        'popularity',
        'genres',
        'parent_genres',
        'dance',
        'acoustic',
        'instrumental',
        'valence',
        'speech',
        'live',
        'loud_db',
        'key',
        'time_signature',
        'label',
        'isrc',
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
            'genres' => 'array',
            'parent_genres' => 'array',
            'loud_db' => 'decimal:2',
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
     * Get the album that this track belongs to.
     */
    public function albumRelation(): BelongsTo
    {
        return $this->belongsTo(Album::class, 'album_id');
    }

    /**
     * Get the artists for this track.
     */
    public function artistsRelation(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class, 'track_artist')
            ->withTimestamps();
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
