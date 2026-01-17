<?php

namespace App\Http\Controllers\Playlists;

use App\Http\Controllers\Controller;
use App\Http\Requests\Playlists\StorePlaylistRequest;
use App\Http\Requests\Playlists\UpdatePlaylistRequest;
use App\Http\Requests\Tracks\ImportSpotifyTrackRequest;
use App\Models\Album;
use App\Models\Artist;
use App\Models\Playlist;
use App\Services\SpotifyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlaylistsController extends Controller
{
    /**
     * Show the user's playlists list page.
     */
    public function index(Request $request): Response
    {
        $playlists = auth()->user()->playlists()
            ->with(['tracks' => function ($query) {
                $query->with(['artistsRelation', 'albumRelation'])->limit(5);
            }])
            ->withCount('tracks')
            ->latest()
            ->get();

        return Inertia::render('playlists/List', [
            'playlists' => $playlists,
        ]);
    }

    /**
     * Show the form for creating a new playlist.
     */
    public function create(): Response
    {
        $tracks = auth()->user()->tracks()
            ->with(['artistsRelation', 'albumRelation'])
            ->orderBy('name')
            ->get();

        return Inertia::render('playlists/Create', [
            'tracks' => $tracks,
        ]);
    }

    /**
     * Store a newly created playlist.
     */
    public function store(StorePlaylistRequest $request): RedirectResponse
    {
        $playlist = Playlist::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'description' => $request->description,
            'is_public' => $request->is_public ?? false,
        ]);

        if ($request->has('track_ids') && is_array($request->track_ids)) {
            $trackIds = array_filter($request->track_ids);
            if (!empty($trackIds)) {
                // Ensure tracks belong to the user
                $validTrackIds = auth()->user()->tracks()
                    ->whereIn('id', $trackIds)
                    ->pluck('id')
                    ->toArray();

                $playlist->tracks()->attach(
                    $validTrackIds,
                    ['position' => 0] // Will be updated with proper positions
                );

                // Update positions
                foreach ($validTrackIds as $index => $trackId) {
                    $playlist->tracks()->updateExistingPivot($trackId, ['position' => $index + 1]);
                }
            }
        }

        return redirect()->route('playlists.index')
            ->with('success', 'Playlist created successfully!');
    }

    /**
     * Display the specified playlist.
     */
    public function show(Playlist $playlist): Response
    {
        // Ensure the playlist belongs to the authenticated user
        $playlist = auth()->user()->playlists()
            ->with(['tracks' => function ($query) {
                $query->with(['artistsRelation', 'albumRelation'])
                      ->orderBy('playlist_track.position');
            }])
            ->findOrFail($playlist->id);

        return Inertia::render('playlists/Show', [
            'playlist' => $playlist,
        ]);
    }

    /**
     * Show the form for editing the specified playlist.
     */
    public function edit(Playlist $playlist): Response
    {
        // Ensure the playlist belongs to the authenticated user
        $playlist = auth()->user()->playlists()
            ->with(['tracks' => function ($query) {
                $query->with(['artistsRelation', 'albumRelation'])
                      ->orderBy('playlist_track.position');
            }])
            ->findOrFail($playlist->id);

        $tracks = auth()->user()->tracks()
            ->with(['artistsRelation', 'albumRelation'])
            ->orderBy('name')
            ->get();

        return Inertia::render('playlists/Edit', [
            'playlist' => $playlist,
            'tracks' => $tracks,
        ]);
    }

    /**
     * Update the specified playlist.
     */
    public function update(UpdatePlaylistRequest $request, Playlist $playlist): RedirectResponse
    {
        // Ensure the playlist belongs to the authenticated user
        $playlist = auth()->user()->playlists()->findOrFail($playlist->id);

        $playlist->update([
            'name' => $request->name,
            'description' => $request->description,
            'is_public' => $request->is_public ?? false,
        ]);

        if ($request->has('track_ids') && is_array($request->track_ids)) {
            $trackIds = array_filter($request->track_ids);

            // Ensure tracks belong to the user
            $validTrackIds = auth()->user()->tracks()
                ->whereIn('id', $trackIds)
                ->pluck('id')
                ->toArray();

            // Sync tracks with new positions
            $syncData = [];
            foreach ($validTrackIds as $index => $trackId) {
                $syncData[$trackId] = ['position' => $index + 1];
            }

            $playlist->tracks()->sync($syncData);
        } else {
            // Remove all tracks if none provided
            $playlist->tracks()->detach();
        }

        return redirect()->route('playlists.show', $playlist)
            ->with('success', 'Playlist updated successfully!');
    }

    /**
     * Remove the specified playlist.
     */
    public function destroy(Playlist $playlist): RedirectResponse
    {
        // Ensure the playlist belongs to the authenticated user
        $playlist = auth()->user()->playlists()->findOrFail($playlist->id);

        $playlist->delete();

        return redirect()->route('playlists.index')
            ->with('success', 'Playlist deleted successfully!');
    }

    /**
     * Add a track to the playlist.
     */
    public function addTrack(Request $request, Playlist $playlist): RedirectResponse
    {
        $request->validate([
            'track_id' => 'required|exists:tracks,id',
        ]);

        // Ensure the playlist belongs to the authenticated user
        $playlist = auth()->user()->playlists()->findOrFail($playlist->id);

        // Ensure the track belongs to the user
        $track = auth()->user()->tracks()->findOrFail($request->track_id);

        // Check if track is already in playlist
        if ($playlist->tracks()->where('track_id', $track->id)->exists()) {
            return redirect()->back()
                ->with('error', 'Track is already in this playlist.');
        }

        // Get next position
        $nextPosition = $playlist->tracks()->max('playlist_track.position') + 1;

        $playlist->tracks()->attach($track->id, ['position' => $nextPosition]);

        return redirect()->back()
            ->with('success', 'Track added to playlist successfully!');
    }

    /**
     * Import and add a track to the playlist.
     */
    public function importAndAddTrack(ImportSpotifyTrackRequest $request, Playlist $playlist, SpotifyService $spotifyService): RedirectResponse
    {
        try {
            // Ensure the playlist belongs to the authenticated user
            $playlist = auth()->user()->playlists()->findOrFail($playlist->id);

            $url = $request->validated()['url'];
            $trackData = $spotifyService->getTrack($url);

            // Find or create the track
            $track = \App\Models\Track::where('user_id', auth()->id())
                ->where('spotify_id', $trackData['spotify_id'])
                ->first();

            if (!$track) {
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
                $track = \App\Models\Track::create([
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
            }

            // Check if track is already in playlist
            if ($playlist->tracks()->where('track_id', $track->id)->exists()) {
                return redirect()->back()
                    ->with('error', 'Track is already in this playlist.');
            }

            // Get next position
            $nextPosition = $playlist->tracks()->max('playlist_track.position') + 1;

            $playlist->tracks()->attach($track->id, ['position' => $nextPosition]);

            return redirect()->back()
                ->with('success', 'Track imported and added to playlist successfully!');
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
     * Remove a track from the playlist.
     */
    public function removeTrack(Playlist $playlist, $trackId): RedirectResponse
    {
        // Ensure the playlist belongs to the authenticated user
        $playlist = auth()->user()->playlists()->findOrFail($playlist->id);

        // Ensure the track belongs to the user and is in the playlist
        $track = auth()->user()->tracks()
            ->whereHas('playlists', function ($query) use ($playlist) {
                $query->where('playlist_id', $playlist->id);
            })
            ->findOrFail($trackId);

        $playlist->tracks()->detach($track->id);

        // Reorder remaining tracks
        $remainingTracks = $playlist->tracks()->orderBy('playlist_track.position')->get();
        foreach ($remainingTracks as $index => $track) {
            $playlist->tracks()->updateExistingPivot($track->id, ['position' => $index + 1]);
        }

        return redirect()->back()
            ->with('success', 'Track removed from playlist successfully!');
    }
}
