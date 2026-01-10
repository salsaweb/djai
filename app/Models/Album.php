<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Album extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'spotify_id',
        'album_art_url',
    ];

    /**
     * Get the tracks for this album.
     */
    public function tracks(): HasMany
    {
        return $this->hasMany(Track::class);
    }
}
