<?php

namespace App\Http\Controllers\Tracks;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tracks\ImportCsvRequest;
use App\Http\Requests\Tracks\ImportSpotifyTrackRequest;
use App\Models\Album;
use App\Models\Artist;
use App\Models\Track;
use App\Services\SpotifyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TracksController extends Controller
{
    /**
     * Show the user's tracks list page.
     */
    public function edit(Request $request): Response
    {
        $query = auth()->user()->tracks()->with(['artistsRelation', 'albumRelation'])->latest();

        // Filter by artist if provided
        if ($request->has('artist_id')) {
            $query->whereHas('artistsRelation', function ($q) use ($request) {
                $q->where('artists.id', $request->artist_id);
            });
        }

        // Filter by album if provided
        if ($request->has('album_id')) {
            $query->where('album_id', $request->album_id);
        }

        // Filter by BPM
        if ($request->has('bpm_min')) {
            $query->where('bpm', '>=', $request->bpm_min);
        }
        if ($request->has('bpm_max')) {
            $query->where('bpm', '<=', $request->bpm_max);
        }

        // Filter by Energy
        if ($request->has('energy_min')) {
            $query->where('energy', '>=', $request->energy_min);
        }
        if ($request->has('energy_max')) {
            $query->where('energy', '<=', $request->energy_max);
        }

        // Filter by Popularity
        if ($request->has('popularity_min')) {
            $query->where('popularity', '>=', $request->popularity_min);
        }
        if ($request->has('popularity_max')) {
            $query->where('popularity', '<=', $request->popularity_max);
        }

        // Filter by Genres (contains)
        if ($request->has('genre')) {
            $query->whereJsonContains('genres', $request->genre);
        }

        // Filter by Parent Genres (contains)
        if ($request->has('parent_genre')) {
            $query->whereJsonContains('parent_genres', $request->parent_genre);
        }

        // Filter by Key
        if ($request->has('key')) {
            $query->where('key', $request->key);
        }

        // Filter by Camelot
        if ($request->has('camelot')) {
            $query->where('camelot', $request->camelot);
        }

        // Filter by Label
        if ($request->has('label')) {
            $query->where('label', 'like', '%' . $request->label . '%');
        }

        // Filter by Dance
        if ($request->has('dance_min')) {
            $query->where('dance', '>=', $request->dance_min);
        }
        if ($request->has('dance_max')) {
            $query->where('dance', '<=', $request->dance_max);
        }

        // Filter by Acoustic
        if ($request->has('acoustic_min')) {
            $query->where('acoustic', '>=', $request->acoustic_min);
        }
        if ($request->has('acoustic_max')) {
            $query->where('acoustic', '<=', $request->acoustic_max);
        }

        // Filter by Instrumental
        if ($request->has('instrumental_min')) {
            $query->where('instrumental', '>=', $request->instrumental_min);
        }
        if ($request->has('instrumental_max')) {
            $query->where('instrumental', '<=', $request->instrumental_max);
        }

        // Filter by Valence
        if ($request->has('valence_min')) {
            $query->where('valence', '>=', $request->valence_min);
        }
        if ($request->has('valence_max')) {
            $query->where('valence', '<=', $request->valence_max);
        }

        // Filter by Speech
        if ($request->has('speech_min')) {
            $query->where('speech', '>=', $request->speech_min);
        }
        if ($request->has('speech_max')) {
            $query->where('speech', '<=', $request->speech_max);
        }

        // Filter by Live
        if ($request->has('live_min')) {
            $query->where('live', '>=', $request->live_min);
        }
        if ($request->has('live_max')) {
            $query->where('live', '<=', $request->live_max);
        }

        // Filter by Loud (Db)
        if ($request->has('loud_db_min')) {
            $query->where('loud_db', '>=', $request->loud_db_min);
        }
        if ($request->has('loud_db_max')) {
            $query->where('loud_db', '<=', $request->loud_db_max);
        }

        // Filter by Time Signature
        if ($request->has('time_signature')) {
            $query->where('time_signature', $request->time_signature);
        }

        // Filter by ISRC
        if ($request->has('isrc')) {
            $query->where('isrc', 'like', '%' . $request->isrc . '%');
        }

        $tracks = $query->get();

        // Get all unique artists and albums for filtering
        $artists = Artist::whereHas('tracks', function ($q) {
            $q->where('user_id', auth()->id());
        })->orderBy('name')->get();

        $albums = Album::whereHas('tracks', function ($q) {
            $q->where('user_id', auth()->id());
        })->orderBy('name')->get();

        // Get unique values for filter dropdowns
        $uniqueGenres = Track::where('user_id', auth()->id())
            ->whereNotNull('genres')
            ->get()
            ->pluck('genres')
            ->flatten()
            ->unique()
            ->filter()
            ->sort()
            ->values();

        $uniqueParentGenres = Track::where('user_id', auth()->id())
            ->whereNotNull('parent_genres')
            ->get()
            ->pluck('parent_genres')
            ->flatten()
            ->unique()
            ->filter()
            ->sort()
            ->values();

        $uniqueKeys = Track::where('user_id', auth()->id())
            ->whereNotNull('key')
            ->distinct()
            ->pluck('key')
            ->filter()
            ->sort()
            ->values();

        $uniqueCamelots = Track::where('user_id', auth()->id())
            ->whereNotNull('camelot')
            ->distinct()
            ->pluck('camelot')
            ->filter()
            ->sort()
            ->values();

        $uniqueTimeSignatures = Track::where('user_id', auth()->id())
            ->whereNotNull('time_signature')
            ->distinct()
            ->pluck('time_signature')
            ->filter()
            ->sort()
            ->values();

        return Inertia::render('tracks/List', [
            'tracks' => $tracks,
            'artists' => $artists,
            'albums' => $albums,
            'filterOptions' => [
                'genres' => $uniqueGenres,
                'parent_genres' => $uniqueParentGenres,
                'keys' => $uniqueKeys,
                'camelots' => $uniqueCamelots,
                'time_signatures' => $uniqueTimeSignatures,
            ],
            'filters' => [
                'artist_id' => $request->artist_id,
                'album_id' => $request->album_id,
                'bpm_min' => $request->bpm_min,
                'bpm_max' => $request->bpm_max,
                'energy_min' => $request->energy_min,
                'energy_max' => $request->energy_max,
                'popularity_min' => $request->popularity_min,
                'popularity_max' => $request->popularity_max,
                'genre' => $request->genre,
                'parent_genre' => $request->parent_genre,
                'key' => $request->key,
                'camelot' => $request->camelot,
                'label' => $request->label,
                'dance_min' => $request->dance_min,
                'dance_max' => $request->dance_max,
                'acoustic_min' => $request->acoustic_min,
                'acoustic_max' => $request->acoustic_max,
                'instrumental_min' => $request->instrumental_min,
                'instrumental_max' => $request->instrumental_max,
                'valence_min' => $request->valence_min,
                'valence_max' => $request->valence_max,
                'speech_min' => $request->speech_min,
                'speech_max' => $request->speech_max,
                'live_min' => $request->live_min,
                'live_max' => $request->live_max,
                'loud_db_min' => $request->loud_db_min,
                'loud_db_max' => $request->loud_db_max,
                'time_signature' => $request->time_signature,
                'isrc' => $request->isrc,
            ],
        ]);
    }

    /**
     * Import a new track to the user's library.
     */
    public function importSpotifyTrack(ImportSpotifyTrackRequest $request, SpotifyService $spotifyService): RedirectResponse
    {
        try {
            $url = $request->validated()['url'];
            $trackData = $spotifyService->getTrack($url);

            // Check if track already exists for this user
            $existingTrack = Track::where('user_id', auth()->id())
                ->where('spotify_id', $trackData['spotify_id'])
                ->first();

            if ($existingTrack) {
                return redirect()->route('tracks.edit')
                    ->with('error', 'This track is already in your library.');
            }

            // Create or update album
            $album = null;
            if (!empty($trackData['album_data'])) {
                $album = Album::firstOrCreate(
                    ['spotify_id' => $trackData['album_data']['spotify_id']],
                    [
                        'name' => $trackData['album_data']['name'],
                        'album_art_url' => $trackData['album_data']['album_art_url'],
                    ]
                );
            }

            // Create the track
            $track = Track::create([
                'user_id' => auth()->id(),
                'album_id' => $album?->id,
                'spotify_id' => $trackData['spotify_id'],
                'name' => $trackData['name'],
                'artists' => $trackData['artists'],
                'album' => $trackData['album'],
                'album_art_url' => $trackData['album_art_url'],
                'duration_ms' => $trackData['duration_ms'],
                'spotify_url' => $trackData['spotify_url'],
                'preview_url' => $trackData['preview_url'],
            ]);

            // Create or update artists and attach to track
            if (!empty($trackData['artists_data'])) {
                $artistIds = [];
                foreach ($trackData['artists_data'] as $artistData) {
                    $artist = Artist::firstOrCreate(
                        ['spotify_id' => $artistData['spotify_id']],
                        ['name' => $artistData['name']]
                    );
                    $artistIds[] = $artist->id;
                }
                $track->artistsRelation()->sync($artistIds);
            }

            return redirect()->route('tracks.edit')
                ->with('success', 'Track imported successfully!');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->withErrors(['url' => $e->getMessage()])
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['url' => 'Failed to import track. Please check the URL and try again.'])
                ->withInput();
        }
    }

    /**
     * Show a single track.
     */
    public function show(Request $request, Track $track): Response
    {
        // Ensure the track belongs to the authenticated user
        $track = auth()->user()->tracks()
            ->with(['relatedTracks', 'relatedTo', 'artistsRelation', 'albumRelation', 'playlists' => function ($query) {
                $query->withCount('tracks');
            }])
            ->findOrFail($track->id);

        // Merge both directions of relationships and ensure we don't include the track itself
        $allRelatedTracks = $track->relatedTracks
            ->merge($track->relatedTo)
            ->reject(fn ($relatedTrack) => $relatedTrack->id === $track->id)
            ->unique('id')
            ->values();

        // Add the merged relationships to the track
        // Using setRelation with 'relatedTracks' will serialize as 'related_tracks' in JSON
        $track->setRelation('relatedTracks', $allRelatedTracks);

        return Inertia::render('tracks/Show', [
            'track' => $track,
        ]);
    }

    /**
     * Import and relate a track to the current track.
     */
    public function importAndRelateTrack(ImportSpotifyTrackRequest $request, Track $track, SpotifyService $spotifyService): RedirectResponse
    {
        try {
            // Ensure the track belongs to the authenticated user
            $track = auth()->user()->tracks()->findOrFail($track->id);

            $url = $request->validated()['url'];
            $trackData = $spotifyService->getTrack($url);

            // Check if the track being imported is the same as the current track
            if ($track->spotify_id === $trackData['spotify_id']) {
                return redirect()->back()
                    ->withErrors(['url' => 'Cannot relate a track to itself.'])
                    ->withInput();
            }

            // Find or create the related track
            $relatedTrack = Track::where('user_id', auth()->id())
                ->where('spotify_id', $trackData['spotify_id'])
                ->first();

            if (!$relatedTrack) {
                // Create or update album
                $album = null;
                if (!empty($trackData['album_data'])) {
                    $album = Album::firstOrCreate(
                        ['spotify_id' => $trackData['album_data']['spotify_id']],
                        [
                            'name' => $trackData['album_data']['name'],
                            'album_art_url' => $trackData['album_data']['album_art_url'],
                        ]
                    );
                }

                // Import the track if it doesn't exist
                $relatedTrack = Track::create([
                    'user_id' => auth()->id(),
                    'album_id' => $album?->id,
                    'spotify_id' => $trackData['spotify_id'],
                    'name' => $trackData['name'],
                    'artists' => $trackData['artists'],
                    'album' => $trackData['album'],
                    'album_art_url' => $trackData['album_art_url'],
                    'duration_ms' => $trackData['duration_ms'],
                    'spotify_url' => $trackData['spotify_url'],
                    'preview_url' => $trackData['preview_url'],
                ]);

                // Create or update artists and attach to track
                if (!empty($trackData['artists_data'])) {
                    $artistIds = [];
                    foreach ($trackData['artists_data'] as $artistData) {
                        $artist = Artist::firstOrCreate(
                            ['spotify_id' => $artistData['spotify_id']],
                            ['name' => $artistData['name']]
                        );
                        $artistIds[] = $artist->id;
                    }
                    $relatedTrack->artistsRelation()->sync($artistIds);
                }
            }

            // Check if the relationship already exists in either direction
            $relationshipExists = $track->relatedTracks()
                    ->where('related_track_id', $relatedTrack->id)
                    ->exists()
                || $relatedTrack->relatedTracks()
                    ->where('related_track_id', $track->id)
                    ->exists();

            if ($relationshipExists) {
                return redirect()->back()
                    ->with('error', 'This track is already related to the current track.');
            }

            // Add the relationship in both directions (bidirectional)
            $track->relatedTracks()->attach($relatedTrack->id);
            $relatedTrack->relatedTracks()->attach($track->id);

            return redirect()->back()
                ->with('success', 'Track imported and added as related track successfully!');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->withErrors(['url' => $e->getMessage()])
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['url' => 'Failed to import track. Please check the URL and try again.'])
                ->withInput();
        }
    }

    /**
     * Remove a related track from the current track.
     */
    public function removeRelatedTrack(Track $track, Track $relatedTrack): RedirectResponse
    {
        // Ensure both tracks belong to the authenticated user
        $track = auth()->user()->tracks()->findOrFail($track->id);
        $relatedTrack = auth()->user()->tracks()->findOrFail($relatedTrack->id);

        // Remove the relationship in both directions (bidirectional)
        $track->relatedTracks()->detach($relatedTrack->id);
        $relatedTrack->relatedTracks()->detach($track->id);

        return redirect()->back()
            ->with('success', 'Related track removed successfully!');
    }

    /**
     * Delete a track.
     */
    public function destroy(Track $track): RedirectResponse
    {
        // Ensure the track belongs to the authenticated user
        $track = auth()->user()->tracks()->findOrFail($track->id);

        $track->delete();

        return redirect()->route('tracks.edit')
            ->with('success', 'Track deleted successfully!');
    }

    /**
     * Import CSV file and update tracks with metadata.
     */
    public function importCsv(ImportCsvRequest $request): RedirectResponse
    {
        try {
            $file = $request->file('csv_file');
            $path = $file->getRealPath();
            
            // Read CSV file
            $csvData = [];
            if (($handle = fopen($path, 'r')) !== false) {
                // Skip the first line (header)
                $header = fgetcsv($handle);
                
                while (($row = fgetcsv($handle)) !== false) {
                    // Skip rows that start with # (comments) or have insufficient columns
                    if (empty($row) || (isset($row[0]) && str_starts_with(trim($row[0]), '#'))) {
                        continue;
                    }
                    
                    if (count($row) >= 25) {
                        $csvData[] = [
                            'spotify_track_id' => trim($row[22] ?? ''),
                            'bpm' => $this->parseInteger($row[3] ?? ''),
                            'camelot' => trim($row[4] ?? ''),
                            'energy' => $this->parseInteger($row[5] ?? ''),
                            'popularity' => $this->parseInteger($row[8] ?? ''),
                            'genres' => $this->parseArray($row[9] ?? ''),
                            'parent_genres' => $this->parseArray($row[10] ?? ''),
                            'dance' => $this->parseInteger($row[13] ?? ''),
                            'acoustic' => $this->parseInteger($row[14] ?? ''),
                            'instrumental' => $this->parseInteger($row[15] ?? ''),
                            'valence' => $this->parseInteger($row[16] ?? ''),
                            'speech' => $this->parseInteger($row[17] ?? ''),
                            'live' => $this->parseInteger($row[18] ?? ''),
                            'loud_db' => $this->parseDecimal($row[19] ?? ''),
                            'key' => trim($row[20] ?? ''),
                            'time_signature' => trim($row[21] ?? ''),
                            'label' => trim($row[23] ?? ''),
                            'isrc' => trim($row[24] ?? ''),
                        ];
                    }
                }
                fclose($handle);
            }

            // Update tracks
            $updated = 0;
            $notFound = 0;
            
            DB::transaction(function () use ($csvData, &$updated, &$notFound) {
                foreach ($csvData as $row) {
                    if (empty($row['spotify_track_id'])) {
                        continue;
                    }

                    $track = Track::where('user_id', auth()->id())
                        ->where('spotify_id', $row['spotify_track_id'])
                        ->first();

                    if ($track) {
                        $track->update([
                            'bpm' => $row['bpm'],
                            'camelot' => $row['camelot'],
                            'energy' => $row['energy'],
                            'popularity' => $row['popularity'],
                            'genres' => $row['genres'],
                            'parent_genres' => $row['parent_genres'],
                            'dance' => $row['dance'],
                            'acoustic' => $row['acoustic'],
                            'instrumental' => $row['instrumental'],
                            'valence' => $row['valence'],
                            'speech' => $row['speech'],
                            'live' => $row['live'],
                            'loud_db' => $row['loud_db'],
                            'key' => $row['key'],
                            'time_signature' => $row['time_signature'],
                            'label' => $row['label'],
                            'isrc' => $row['isrc'],
                        ]);
                        $updated++;
                    } else {
                        $notFound++;
                    }
                }
            });

            $message = "CSV imported successfully! Updated {$updated} track(s).";
            if ($notFound > 0) {
                $message .= " {$notFound} track(s) not found in your library.";
            }

            return redirect()->route('tracks.edit')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['csv_file' => 'Failed to import CSV: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Parse integer value from CSV.
     */
    private function parseInteger(?string $value): ?int
    {
        if (empty($value) || $value === '') {
            return null;
        }
        $value = trim($value);
        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * Parse decimal value from CSV.
     */
    private function parseDecimal(?string $value): ?float
    {
        if (empty($value) || $value === '') {
            return null;
        }
        $value = trim($value);
        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * Parse array value from CSV (comma-separated string).
     */
    private function parseArray(?string $value): ?array
    {
        if (empty($value) || $value === '') {
            return null;
        }
        $value = trim($value);
        if (empty($value)) {
            return null;
        }
        // Split by comma and trim each value
        $items = array_map('trim', explode(',', $value));
        return array_filter($items); // Remove empty values
    }
}
