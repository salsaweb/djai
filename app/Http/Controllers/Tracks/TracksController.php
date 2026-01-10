<?php

namespace App\Http\Controllers\Tracks;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tracks\ImportSpotifyTrackRequest;
use App\Models\Track;
use App\Services\SpotifyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TracksController extends Controller
{
    /**
     * Show the user's tracks list page.
     */
    public function edit(): Response
    {
        $tracks = auth()->user()->tracks()->latest()->get();

        return Inertia::render('tracks/List', [
            'tracks' => $tracks,
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

            // Create the track
            Track::create([
                'user_id' => auth()->id(),
                ...$trackData,
            ]);

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
            ->with(['relatedTracks', 'relatedTo'])
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
                // Import the track if it doesn't exist
                $relatedTrack = Track::create([
                    'user_id' => auth()->id(),
                    ...$trackData,
                ]);
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
}
