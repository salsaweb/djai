<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { create, show, destroy } from '@/routes/playlists';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Music, Plus, Trash2 } from 'lucide-vue-next';

interface Playlist {
    id: number;
    name: string;
    description: string | null;
    is_public: boolean;
    tracks_count: number;
    tracks: Array<{
        id: number;
        name: string;
        artists: string[];
        album: string;
        album_art_url: string;
    }>;
    created_at: string;
}

interface Props {
    playlists: Playlist[];
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Playlists',
        href: '/playlists/list',
    },
];
</script>

<template>
    <Head title="Playlists" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Playlists</h1>
                    <p class="text-muted-foreground">
                        Manage your music playlists
                    </p>
                </div>
                <Link
                    :href="create().url"
                    class="inline-flex items-center gap-2 rounded-md bg-primary px-3 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                >
                    <Plus class="h-4 w-4" />
                    Create Playlist
                </Link>
            </div>

            <div v-if="playlists.length === 0" class="py-12 text-center">
                <Music class="mx-auto h-12 w-12 text-muted-foreground" />
                <h3 class="mt-4 text-lg font-medium">No playlists yet</h3>
                <p class="text-muted-foreground">
                    Create your first playlist to get started.
                </p>
                <Link
                    :href="create().url"
                    class="mt-4 inline-flex items-center gap-2 rounded-md bg-primary px-3 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90"
                >
                    <Plus class="h-4 w-4" />
                    Create Playlist
                </Link>
            </div>

            <div v-else class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="playlist in playlists"
                    :key="playlist.id"
                    class="group relative overflow-hidden rounded-lg border bg-card p-4 shadow-sm transition-colors hover:bg-accent"
                >
                    <Link
                        :href="show(playlist.id).url"
                        class="block"
                    >
                        <div class="flex items-start gap-3">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-md bg-muted"
                            >
                                <Music class="h-6 w-6 text-muted-foreground" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="truncate font-medium">
                                    {{ playlist.name }}
                                </h3>
                                <p
                                    v-if="playlist.description"
                                    class="line-clamp-2 text-sm text-muted-foreground"
                                >
                                    {{ playlist.description }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    {{ playlist.tracks_count }} track{{
                                        playlist.tracks_count !== 1 ? 's' : ''
                                    }}
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="playlist.tracks && playlist.tracks.length > 0"
                            class="mt-3"
                        >
                            <p class="mb-2 text-xs text-muted-foreground">
                                Recent tracks:
                            </p>
                            <div class="space-y-1">
                                <div
                                    v-for="track in playlist.tracks.slice(0, 3)"
                                    :key="track.id"
                                    class="truncate text-xs text-muted-foreground"
                                >
                                    {{ track.name }} -
                                    {{
                                        Array.isArray(track.artists)
                                            ? track.artists.join(', ')
                                            : track.artists
                                    }}
                                </div>
                            </div>
                            <p
                                v-if="playlist.tracks_count > 3"
                                class="mt-1 text-xs text-muted-foreground"
                            >
                                +{{ playlist.tracks_count - 3 }} more
                            </p>
                        </div>
                    </Link>

                    <div
                        class="absolute top-2 right-2 opacity-0 transition-opacity group-hover:opacity-100"
                    >
                        <button
                            @click.stop="$inertia.delete(destroy(playlist.id).url)"
                            class="rounded-full bg-destructive p-1 text-destructive-foreground hover:bg-destructive/90"
                            title="Delete playlist"
                        >
                            <Trash2 class="h-3 w-3" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
