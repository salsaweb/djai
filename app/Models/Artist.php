<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Artist extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'spotify_id',
    ];

    /**
     * Get the tracks for this artist.
     */
    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class, 'track_artist')
            ->withTimestamps();
    }
}
