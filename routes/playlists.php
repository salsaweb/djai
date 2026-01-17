<?php

use App\Http\Controllers\Playlists\PlaylistsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::redirect('playlists', '/playlists/list');

    Route::get('playlists/list', [PlaylistsController::class, 'index'])->name('playlists.index');
    Route::get('playlists/create', [PlaylistsController::class, 'create'])->name('playlists.create');
    Route::post('playlists', [PlaylistsController::class, 'store'])->name('playlists.store');
    Route::get('playlists/{playlist}', [PlaylistsController::class, 'show'])->name('playlists.show');
    Route::get('playlists/{playlist}/edit', [PlaylistsController::class, 'edit'])->name('playlists.edit');
    Route::put('playlists/{playlist}', [PlaylistsController::class, 'update'])->name('playlists.update');
    Route::delete('playlists/{playlist}', [PlaylistsController::class, 'destroy'])->name('playlists.destroy');
    Route::post('playlists/{playlist}/tracks', [PlaylistsController::class, 'addTrack'])->name('playlists.addTrack');
    Route::post('playlists/{playlist}/import-and-add-track', [PlaylistsController::class, 'importAndAddTrack'])->name('playlists.importAndAddTrack');
    Route::delete('playlists/{playlist}/tracks/{trackId}', [PlaylistsController::class, 'removeTrack'])->name('playlists.removeTrack');
});
