<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SpotifyService
{
    private string $clientId;
    private string $clientSecret;
    private string $baseUrl = 'https://api.spotify.com/v1';
    private ?string $accessToken = null;

    public function __construct()
    {
        $this->clientId = config('services.spotify.client_id', '');
        $this->clientSecret = config('services.spotify.client_secret', '');
    }

    /**
     * Get an access token from Spotify API.
     */
    private function getAccessToken(): string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $response = Http::asForm()->withBasicAuth($this->clientId, $this->clientSecret)
            ->post('https://accounts.spotify.com/api/token', [
                'grant_type' => 'client_credentials',
            ]);

        if ($response->failed()) {
            Log::error('Spotify API authentication failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Failed to authenticate with Spotify API');
        }

        $this->accessToken = $response->json('access_token');

        return $this->accessToken;
    }

    /**
     * Extract track ID from a Spotify URL.
     */
    private function extractTrackId(string $url): string
    {
        // Handle various Spotify URL formats:
        // https://open.spotify.com/track/4iV5W9uYEdYUVa79Axb7Rh
        // https://open.spotify.com/track/4iV5W9uYEdYUVa79Axb7Rh?si=...
        // spotify:track:4iV5W9uYEdYUVa79Axb7Rh

        // Try to extract from URL
        if (preg_match('/track\/([a-zA-Z0-9]+)/', $url, $matches)) {
            return $matches[1];
        }

        // Try to extract from spotify: URI
        if (preg_match('/spotify:track:([a-zA-Z0-9]+)/', $url, $matches)) {
            return $matches[1];
        }

        throw new \InvalidArgumentException('Invalid Spotify track URL format');
    }

    /**
     * Get track information from Spotify API.
     *
     * @return array<string, mixed>
     */
    public function getTrack(string $url): array
    {
        $trackId = $this->extractTrackId($url);
        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)
            ->get("{$this->baseUrl}/tracks/{$trackId}");

        if ($response->failed()) {
            Log::error('Spotify API track fetch failed', [
                'track_id' => $trackId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Failed to fetch track from Spotify API');
        }

        $trackData = $response->json();

        return [
            'spotify_id' => $trackData['id'],
            'name' => $trackData['name'],
            'artists' => array_map(fn ($artist) => $artist['name'], $trackData['artists']),
            'artists_data' => array_map(fn ($artist) => [
                'name' => $artist['name'],
                'spotify_id' => $artist['id'],
            ], $trackData['artists']),
            'album' => $trackData['album']['name'] ?? null,
            'album_data' => $trackData['album'] ? [
                'name' => $trackData['album']['name'],
                'spotify_id' => $trackData['album']['id'],
                'album_art_url' => $this->getAlbumArtUrl($trackData['album']['images'] ?? []),
            ] : null,
            'album_art_url' => $this->getAlbumArtUrl($trackData['album']['images'] ?? []),
            'duration_ms' => $trackData['duration_ms'] ?? null,
            'spotify_url' => $trackData['external_urls']['spotify'] ?? $url,
            'preview_url' => $trackData['preview_url'] ?? null,
        ];
    }

    /**
     * Get the best available album art URL.
     */
    private function getAlbumArtUrl(array $images): ?string
    {
        if (empty($images)) {
            return null;
        }

        // Return the medium-sized image (index 1) or the first available
        return $images[1]['url'] ?? $images[0]['url'] ?? null;
    }
}
