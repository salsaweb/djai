<?php

use App\Http\Controllers\Tracks\TracksController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::redirect('tracks', '/tracks/list');

    Route::post('tracks/import-spotify-track', [TracksController::class, 'importSpotifyTrack'])->name('tracks.importSpotifyTrack');
    Route::post('tracks/import-csv', [TracksController::class, 'importCsv'])->name('tracks.importCsv');
    Route::post('tracks/{track}/import-and-relate', [TracksController::class, 'importAndRelateTrack'])->name('tracks.importAndRelate');
    Route::delete('tracks/{track}/related/{relatedTrack}', [TracksController::class, 'removeRelatedTrack'])->name('tracks.removeRelated');
    Route::get('tracks/list', [TracksController::class, 'edit'])->name('tracks.edit');
    Route::get('tracks/{track}', [TracksController::class, 'show'])->name('tracks.show');
    Route::delete('tracks/{track}', [TracksController::class, 'destroy'])->name('tracks.destroy');
});
