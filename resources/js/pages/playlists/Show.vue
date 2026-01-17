<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { index, edit, destroy } from '@/routes/playlists';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage, useForm, Link } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Edit, Trash2, Music, Clock, PlusIcon, CheckCircle2, AlertCircle } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetFooter,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Alert, AlertDescription } from '@/components/ui/alert';
import InputError from '@/components/InputError.vue';

interface Track {
    id: number;
    name: string;
    artists: string[];
    album: string;
    album_art_url: string;
    duration_ms: number;
    artists_relation?: { id: number; name: string }[];
    album_relation?: { id: number; name: string };
}

interface Playlist {
    id: number;
    name: string;
    description: string | null;
    is_public: boolean;
    tracks: Track[];
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    playlist: Playlist;
}>();

const page = usePage();
const flash = computed(() => page.props.flash as { success?: string; error?: string } | undefined);

const deleteDialogOpen = ref(false);
const openImportTrack = ref(false);

const importForm = useForm({
    url: '',
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Playlists',
        href: index().url,
    },
    {
        title: props.playlist.name,
        href: '#',
    },
];

const totalDuration = computed(() => {
    return props.playlist.tracks.reduce((total, track) => total + (track.duration_ms || 0), 0);
});

const formatDuration = (ms: number): string => {
    const seconds = Math.floor(ms / 1000);
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
};

const formatTotalDuration = (ms: number): string => {
    const totalSeconds = Math.floor(ms / 1000);
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);

    if (hours > 0) {
        return `${hours}h ${minutes}m`;
    }
    return `${minutes}m`;
};

const handleDelete = () => {
    router.delete(destroy(props.playlist.id).url, {
        onSuccess: () => {
            deleteDialogOpen.value = false;
        },
    });
};

const handleImportAndAddTrack = () => {
    importForm.post(`/playlists/${props.playlist.id}/import-and-add-track`, {
        onSuccess: () => {
            openImportTrack.value = false;
            importForm.reset();
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="playlist.name" />

        <!-- Flash Messages -->
        <div v-if="flash?.success" class="mb-4">
            <Alert>
                <CheckCircle2 class="h-4 w-4" />
                <AlertDescription>{{ flash.success }}</AlertDescription>
            </Alert>
        </div>
        <div v-if="flash?.error" class="mb-4">
            <Alert variant="destructive">
                <AlertCircle class="h-4 w-4" />
                <AlertDescription>{{ flash.error }}</AlertDescription>
            </Alert>
        </div>

        <div class="flex items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="sm" as-child>
                    <a :href="index().url">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        Back to Playlists
                    </a>
                </Button>
                <div>
                    <h1 class="text-2xl font-bold">{{ playlist.name }}</h1>
                    <p class="text-muted-foreground">
                        {{ playlist.tracks.length }} track{{ playlist.tracks.length !== 1 ? 's' : '' }}
                        <span v-if="totalDuration > 0"> • {{ formatTotalDuration(totalDuration) }}</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <Badge :variant="playlist.is_public ? 'default' : 'secondary'">
                    {{ playlist.is_public ? 'Public' : 'Private' }}
                </Badge>

                <Button variant="outline" size="sm" as-child>
                    <a :href="edit(props.playlist.id).url">
                        <Edit class="h-4 w-4 mr-2" />
                        Edit
                    </a>
                </Button>

                <Dialog v-model:open="deleteDialogOpen">
                    <DialogTrigger as-child>
                        <Button variant="destructive" size="sm">
                            <Trash2 class="h-4 w-4 mr-2" />
                            Delete
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Delete Playlist</DialogTitle>
                            <DialogDescription>
                                Are you sure you want to delete "{{ playlist.name }}"?
                                This action cannot be undone.
                            </DialogDescription>
                        </DialogHeader>
                        <DialogFooter>
                            <Button variant="secondary" @click="deleteDialogOpen = false">
                                Cancel
                            </Button>
                            <Button variant="destructive" @click="handleDelete">
                                Delete Playlist
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Playlist Details -->
            <div class="lg:col-span-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Playlist Details</CardTitle>
                        <CardDescription>
                            {{ playlist.description || 'No description provided.' }}
                        </CardDescription>
                    </CardHeader>
                </Card>

                <!-- Tracks List -->
                <Card class="mt-6">
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle>Tracks</CardTitle>
                                <CardDescription>
                                    Songs in this playlist
                                </CardDescription>
                            </div>
                            <Sheet v-model:open="openImportTrack">
                                <SheetTrigger as-child>
                                    <Button variant="outline" size="sm">
                                        <PlusIcon class="w-4 h-4 mr-2" />
                                        Add Track
                                    </Button>
                                </SheetTrigger>
                                <SheetContent>
                                    <SheetHeader>
                                        <SheetTitle>Import Track to Playlist</SheetTitle>
                                        <SheetDescription>
                                            Import a track from Spotify and add it to this playlist.
                                        </SheetDescription>
                                    </SheetHeader>
                                    <form @submit.prevent="handleImportAndAddTrack" class="space-y-6">
                                        <div class="grid flex-1 auto-rows-min gap-6 px-4">
                                            <div class="grid gap-3">
                                                <Label for="url">Spotify URL</Label>
                                                <Input
                                                    id="url"
                                                    type="text"
                                                    v-model="importForm.url"
                                                    placeholder="https://open.spotify.com/track/1234567890"
                                                    :class="{ 'border-red-500': importForm.errors.url }"
                                                />
                                                <InputError :message="importForm.errors.url" />
                                            </div>
                                        </div>

                                        <SheetFooter>
                                            <div class="w-full p-4 flex justify-between">
                                                <Button
                                                    type="button"
                                                    variant="outline"
                                                    class="w-[45%] mr-4"
                                                    @click="openImportTrack = false"
                                                >
                                                    Cancel
                                                </Button>
                                                <Button type="submit" :disabled="importForm.processing" class="w-[45%]">
                                                    {{ importForm.processing ? 'Importing...' : 'Import & Add' }}
                                                </Button>
                                            </div>
                                        </SheetFooter>
                                    </form>
                                </SheetContent>
                            </Sheet>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div v-if="playlist.tracks.length === 0" class="text-center py-8">
                            <Music class="mx-auto h-8 w-8 text-muted-foreground" />
                            <p class="text-sm text-muted-foreground mt-2">
                                No tracks in this playlist yet.
                            </p>
                        </div>
                        <div v-else class="space-y-2">
                            <div
                                v-for="(track, index) in playlist.tracks"
                                :key="track.id"
                                class="flex items-center gap-3 p-3 border rounded-lg"
                            >
                                <div class="flex items-center justify-center w-8 h-8 bg-muted rounded-full text-sm font-medium">
                                    {{ index + 1 }}
                                </div>
                                <img
                                    v-if="track.album_art_url"
                                    :src="track.album_art_url"
                                    :alt="track.album"
                                    class="w-10 h-10 rounded object-cover"
                                />
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium truncate">
                                         <Link :href="`/tracks/${track.id}`">{{ track.name }}</Link>
                                    </p>
                                    <p class="text-sm text-muted-foreground truncate">
                                        {{ track.artists_relation ? track.artists_relation.map(a => a.name).join(', ') : track.artists.join(', ') }}
                                    </p>
                                </div>
                                <div class="text-sm text-muted-foreground">
                                    <Clock class="inline w-3 h-3 mr-1" />
                                    {{ formatDuration(track.duration_ms) }}
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Playlist Stats -->
            <div>
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg">Statistics</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex justify-between">
                            <span class="text-sm text-muted-foreground">Total Tracks</span>
                            <span class="font-medium">{{ playlist.tracks.length }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-muted-foreground">Total Duration</span>
                            <span class="font-medium">{{ formatTotalDuration(totalDuration) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-muted-foreground">Visibility</span>
                            <Badge :variant="playlist.is_public ? 'default' : 'secondary'" class="text-xs">
                                {{ playlist.is_public ? 'Public' : 'Private' }}
                            </Badge>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-muted-foreground">Created</span>
                            <span class="font-medium text-sm">
                                {{ new Date(playlist.created_at).toLocaleDateString() }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-muted-foreground">Last Updated</span>
                            <span class="font-medium text-sm">
                                {{ new Date(playlist.updated_at).toLocaleDateString() }}
                            </span>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
