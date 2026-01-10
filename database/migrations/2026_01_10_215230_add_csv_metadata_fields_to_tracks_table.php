<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tracks', function (Blueprint $table) {
            $table->integer('bpm')->nullable()->after('duration_ms');
            $table->string('camelot')->nullable()->after('bpm');
            $table->integer('energy')->nullable()->after('camelot');
            $table->integer('popularity')->nullable()->after('energy');
            $table->text('genres')->nullable()->after('popularity'); // JSON array
            $table->text('parent_genres')->nullable()->after('genres'); // JSON array
            $table->integer('dance')->nullable()->after('parent_genres');
            $table->integer('acoustic')->nullable()->after('dance');
            $table->integer('instrumental')->nullable()->after('acoustic');
            $table->integer('valence')->nullable()->after('instrumental');
            $table->integer('speech')->nullable()->after('valence');
            $table->integer('live')->nullable()->after('speech');
            $table->decimal('loud_db', 5, 2)->nullable()->after('live');
            $table->string('key')->nullable()->after('loud_db');
            $table->string('time_signature')->nullable()->after('key');
            $table->string('label')->nullable()->after('time_signature');
            $table->string('isrc')->nullable()->after('label');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tracks', function (Blueprint $table) {
            $table->dropColumn([
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
            ]);
        });
    }
};
