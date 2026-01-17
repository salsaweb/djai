<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { index, create, store } from '@/routes/playlists';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Form, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import InputError from '@/components/InputError.vue';
import { ArrowLeft, Music, Plus, X } from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface Track {
    id: number;
    name: string;
    artists: string[];
    album: string;
    album_art_url: string;
    duration_ms: number;
}

interface Props {
    tracks: Track[];
}

const props = defineProps<Props>();

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
        title: 'Create Playlist',
        href: create().url,
    },
];

const form = ref({
    name: '',
    description: '',
    is_public: false,
    track_ids: [] as number[],
    errors: {} as Record<string, string>,
    processing: false,
});

const selectedTracks = computed(() => {
    return props.tracks.filter(track => form.value.track_ids.includes(track.id));
});

const addTrack = (trackId: number) => {
    if (!form.value.track_ids.includes(trackId)) {
        form.value.track_ids.push(trackId);
    }
};

const removeTrack = (trackId: number) => {
    const index = form.value.track_ids.indexOf(trackId);
    if (index > -1) {
        form.value.track_ids.splice(index, 1);
    }
};

const formatDuration = (ms: number): string => {
    const seconds = Math.floor(ms / 1000);
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
};

const submit = () => {
    router.post(store().url, form.value, {
        onStart: () => {
            form.value.processing = true;
            form.value.errors = {};
        },
        onFinish: () => {
            form.value.processing = false;
        },
        onError: (errors) => {
            form.value.errors = errors;
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Create Playlist" />

        <div class="flex items-center gap-4 mb-6">
            <Button variant="ghost" size="sm" as-child>
                <a :href="index().url">
                    <ArrowLeft class="h-4 w-4 mr-2" />
                    Back to Playlists
                </a>
            </Button>
            <div>
                <h1 class="text-2xl font-bold">Create New Playlist</h1>
                <p class="text-muted-foreground">Create a playlist and add tracks to it</p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Playlist Form -->
            <div class="lg:col-span-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Playlist Details</CardTitle>
                        <CardDescription>
                            Enter the basic information for your playlist
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <Form
                            v-bind="store.form()"
                            @submit.prevent="submit"
                            class="space-y-6"
                        >
                            <div class="space-y-2">
                                <Label for="name">Name *</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    placeholder="My Awesome Playlist"
                                    required
                                />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div class="space-y-2">
                                <Label for="description">Description</Label>
                                <Textarea
                                    id="description"
                                    v-model="form.description"
                                    placeholder="A description of your playlist..."
                                    rows="3"
                                />
                                <InputError :message="form.errors.description" />
                            </div>

                            <div class="flex items-center space-x-2">
                                <Checkbox
                                    id="is_public"
                                    v-model:checked="form.is_public"
                                />
                                <Label for="is_public" class="text-sm font-medium">
                                    Make playlist public
                                </Label>
                            </div>

                            <div class="space-y-2">
                                <Label>Selected Tracks ({{ selectedTracks.length }})</Label>
                                <div v-if="selectedTracks.length === 0" class="text-sm text-muted-foreground">
                                    No tracks selected yet. Add tracks from the list below.
                                </div>
                                <div v-else class="space-y-2">
                                    <div
                                        v-for="track in selectedTracks"
                                        :key="track.id"
                                        class="flex items-center gap-3 p-3 border rounded-lg"
                                    >
                                        <img
                                            v-if="track.album_art_url"
                                            :src="track.album_art_url"
                                            :alt="track.album"
                                            class="w-10 h-10 rounded object-cover"
                                        />
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium truncate">{{ track.name }}</p>
                                            <p class="text-sm text-muted-foreground truncate">
                                                {{ Array.isArray(track.artists) ? track.artists.join(', ') : track.artists }}
                                            </p>
                                        </div>
                                        <span class="text-sm text-muted-foreground">
                                            {{ formatDuration(track.duration_ms) }}
                                        </span>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="removeTrack(track.id)"
                                        >
                                            <X class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                                <InputError :message="form.errors.track_ids" />
                            </div>

                            <div class="flex justify-end gap-3">
                                <Button
                                    type="button"
                                    variant="outline"
                                    as-child
                                >
                                    <a :href="index().url">Cancel</a>
                                </Button>
                                <Button
                                    type="submit"
                                    :disabled="form.processing"
                                >
                                    {{ form.processing ? 'Creating...' : 'Create Playlist' }}
                                </Button>
                            </div>
                        </Form>
                    </CardContent>
                </Card>
            </div>

            <!-- Available Tracks -->
            <div>
                <Card>
                    <CardHeader>
                        <CardTitle class="text-lg">Available Tracks</CardTitle>
                        <CardDescription>
                            Click to add tracks to your playlist
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="props.tracks.length === 0" class="text-center py-8">
                            <Music class="mx-auto h-8 w-8 text-muted-foreground" />
                            <p class="text-sm text-muted-foreground mt-2">
                                No tracks available. Import some tracks first.
                            </p>
                        </div>
                        <div v-else class="space-y-2 max-h-96 overflow-y-auto">
                            <div
                                v-for="track in props.tracks"
                                :key="track.id"
                                class="flex items-center gap-3 p-2 rounded-lg hover:bg-accent cursor-pointer transition-colors"
                                :class="{ 'bg-accent': form.track_ids.includes(track.id) }"
                                @click="form.track_ids.includes(track.id) ? removeTrack(track.id) : addTrack(track.id)"
                            >
                                <img
                                    v-if="track.album_art_url"
                                    :src="track.album_art_url"
                                    :alt="track.album"
                                    class="w-8 h-8 rounded object-cover"
                                />
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium truncate">{{ track.name }}</p>
                                    <p class="text-xs text-muted-foreground truncate">
                                        {{ Array.isArray(track.artists) ? track.artists.join(', ') : track.artists }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-muted-foreground">
                                        {{ formatDuration(track.duration_ms) }}
                                    </span>
                                    <div
                                        v-if="form.track_ids.includes(track.id)"
                                        class="w-5 h-5 bg-primary rounded-full flex items-center justify-center"
                                    >
                                        <Plus class="h-3 w-3 text-primary-foreground" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
