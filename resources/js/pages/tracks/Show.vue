<script setup lang="ts">
    import { Head, Link, router, usePage, useForm } from '@inertiajs/vue3';
    import { type BreadcrumbItem } from '@/types';
    import { type Track } from '@/types';
    import AppLayout from '@/layouts/AppLayout.vue';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Alert, AlertDescription } from '@/components/ui/alert';
    import {
        Sheet,
        SheetContent,
        SheetDescription,
        SheetFooter,
        SheetHeader,
        SheetTitle,
        SheetTrigger,
    } from '@/components/ui/sheet';
    import InputError from '@/components/InputError.vue';
    import { ArrowLeft, ExternalLink, Trash2, PlusIcon, CheckCircle2, AlertCircle } from 'lucide-vue-next';
    import { edit } from '@/routes/tracks';
    import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
    import { ref, computed } from 'vue';

    const props = defineProps<{
        track: Track;
    }>();

    const page = usePage();
    const flash = computed(() => page.props.flash as { success?: string; error?: string } | undefined);

    const deleteDialogOpen = ref(false);
    const openImportTrack = ref(false);

    const importForm = useForm({
        url: '',
    });

    const formatDuration = (ms: number | null): string => {
        if (!ms) return 'N/A';
        const seconds = Math.floor(ms / 1000);
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;
        return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
    };

    const handleDelete = () => {
        router.delete(`/tracks/${props.track.id}`, {
            onSuccess: () => {
                deleteDialogOpen.value = false;
            },
        });
    };

    const handleImportAndRelate = () => {
        importForm.post(`/tracks/${props.track.id}/import-and-relate`, {
            onSuccess: () => {
                openImportTrack.value = false;
                importForm.reset();
            },
        });
    };

    const handleRemoveRelated = (relatedTrackId: number) => {
        router.delete(`/tracks/${props.track.id}/related/${relatedTrackId}`, {
            preserveScroll: true,
        });
    };

    const breadcrumbItems: BreadcrumbItem[] = [
        {
            title: 'Tracks',
            href: edit().url,
        },
        {
            title: props.track.name,
            href: `/tracks/${props.track.id}`,
        },
    ];
</script>

    <template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="track.name" />

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

        <div class="space-y-6">
            <div class="flex items-center justify-between mt-2">
                <Link :href="edit().url">
                    <Button variant="ghost" size="sm">
                        <ArrowLeft class="w-4 h-4 mr-2" />
                        Back to Tracks
                    </Button>
                </Link>

                <Dialog v-model:open="deleteDialogOpen">
                    <DialogTrigger as-child>
                        <Button variant="destructive" size="sm">
                            <Trash2 class="w-4 h-4 mr-2" />
                            Delete Track
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Are you sure you want to delete this track?</DialogTitle>
                            <DialogDescription>
                                This action cannot be undone. This will permanently delete "{{ track.name }}" from your library.
                            </DialogDescription>
                        </DialogHeader>
                        <DialogFooter>
                            <Button variant="secondary" @click="deleteDialogOpen = false">
                                Cancel
                            </Button>
                            <Button variant="destructive" @click="handleDelete">
                                Delete Track
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>

            <Card>
                <CardHeader>
                    <div class="flex items-start gap-6">
                        <img
                            v-if="track.album_art_url"
                            :src="track.album_art_url"
                            :alt="track.album || track.name"
                            class="w-48 h-48 rounded-lg object-cover"
                        />
                        <div class="flex-1">
                            <CardTitle class="text-3xl mb-2">{{ track.name }}</CardTitle>
                            <CardDescription class="text-lg mb-4">
                                <div class="flex flex-wrap gap-1">
                                    <template v-if="track.artists_relation && track.artists_relation.length > 0">
                                        <Link
                                            v-for="(artist, index) in track.artists_relation"
                                            :key="artist.id"
                                            :href="edit().url + '?artist_id=' + artist.id"
                                            class="text-primary hover:underline"
                                        >
                                            {{ artist.name }}<span v-if="index < track.artists_relation.length - 1">, </span>
                                        </Link>
                                    </template>
                                    <span v-else>{{ track.artists.join(', ') }}</span>
                                </div>
                            </CardDescription>
                            <div class="flex flex-wrap gap-4 text-sm text-muted-foreground">
                                <div v-if="track.album_relation || track.album">
                                    <span class="font-medium">Album:</span>
                                    <Link
                                        v-if="track.album_relation"
                                        :href="edit().url + '?album_id=' + track.album_relation.id"
                                        class="text-primary hover:underline ml-1"
                                    >
                                        {{ track.album_relation.name }}
                                    </Link>
                                    <span v-else>{{ track.album }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">Duration:</span> {{ formatDuration(track.duration_ms) }}
                                </div>
                                <div v-if="track.bpm">
                                    <span class="font-medium">BPM:</span> {{ track.bpm }}
                                </div>
                                <div v-if="track.camelot">
                                    <span class="font-medium">Camelot:</span> {{ track.camelot }}
                                </div>
                                <div v-if="track.energy">
                                    <span class="font-medium">Energy:</span> {{ track.energy }}
                                </div>
                                <div v-if="track.popularity">
                                    <span class="font-medium">Popularity:</span> {{ track.popularity }}
                                </div>
                                <div v-if="track.genres">
                                    <span class="font-medium">Genres:</span> {{ track.genres }}
                                </div>
                                <div v-if="track.parent_genres">
                                    <span class="font-medium">Parent Genres:</span> {{ track.parent_genres }}
                                </div>
                                <div v-if="track.dance">
                                    <span class="font-medium">Dance:</span> {{ track.dance }}
                                </div>
                                <div v-if="track.acoustic">
                                    <span class="font-medium">Acoustic:</span> {{ track.acoustic }}
                                </div>
                                <div v-if="track.instrumental">
                                    <span class="font-medium">Instrumental:</span> {{ track.instrumental }}
                                </div>
                                <div v-if="track.valence">
                                    <span class="font-medium">Valence:</span> {{ track.valence }}
                                </div>
                                <div v-if="track.speech">
                                    <span class="font-medium">Speech:</span> {{ track.speech }}
                                </div>
                                <div v-if="track.live">
                                    <span class="font-medium">Live:</span> {{ track.live }}
                                </div>
                                <div v-if="track.loud_db">
                                    <span class="font-medium">Loud db:</span> {{ track.loud_db }}
                                </div>
                                <div v-if="track.key">
                                    <span class="font-medium">Key:</span> {{ track.key }}
                                </div>
                                <div v-if="track.time_signature">
                                    <span class="font-medium">Time Signature:</span> {{ track.time_signature }}
                                </div>
                                <div v-if="track.label">
                                    <span class="font-medium">Label:</span> {{ track.label }}
                                </div>
                               
                            </div>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="flex gap-4">
                        <a
                            v-if="track.spotify_url"
                            :href="track.spotify_url"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <Button variant="outline">
                                <ExternalLink class="w-4 h-4 mr-2" />
                                Open in Spotify
                            </Button>
                        </a>
                        <audio
                            v-if="track.preview_url"
                            controls
                            class="flex-1"
                        >
                            <source :src="track.preview_url" type="audio/mpeg" />
                            Your browser does not support the audio element.
                        </audio>
                    </div>

                    <div class="pt-4 border-t space-y-2 text-sm text-muted-foreground">
                        <div>
                            <span class="font-medium">Spotify ID:</span> {{ track.spotify_id }}
                        </div>
                        <div>
                            <span class="font-medium">Added:</span> {{ new Date(track.created_at).toLocaleDateString() }}
                        </div>
                        <div>
                            <span class="font-medium">ISRC:</span> {{ track.isrc }}
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Related Tracks Section -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>Related Tracks</CardTitle>
                            <CardDescription>
                                Tracks related to this one
                            </CardDescription>
                        </div>
                        <Sheet v-model:open="openImportTrack">
                            <SheetTrigger as-child>
                                <Button variant="outline" size="sm">
                                    <PlusIcon class="w-4 h-4 mr-2" />
                                    Add Related Track
                                </Button>
                            </SheetTrigger>
                            <SheetContent>
                                <SheetHeader>
                                    <SheetTitle>Import Related Track</SheetTitle>
                                    <SheetDescription>
                                        Import a track from Spotify and add it as a related track.
                                    </SheetDescription>
                                </SheetHeader>
                                <form @submit.prevent="handleImportAndRelate" class="space-y-6">
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
                                                {{ importForm.processing ? 'Importing...' : 'Import & Relate' }}
                                            </Button>
                                        </div>
                                    </SheetFooter>
                                </form>
                            </SheetContent>
                        </Sheet>
                    </div>
                </CardHeader>
                <CardContent>
                    <div v-if="track.related_tracks && track.related_tracks.length > 0" class="space-y-4">
                        <div
                            v-for="relatedTrack in track.related_tracks"
                            :key="relatedTrack.id"
                            class="flex items-center gap-4 p-4 border rounded-lg hover:bg-accent transition-colors"
                        >
                            <img
                                v-if="relatedTrack.album_art_url"
                                :src="relatedTrack.album_art_url"
                                :alt="relatedTrack.album || relatedTrack.name"
                                class="w-16 h-16 rounded object-cover"
                            />
                            <div class="flex-1 min-w-0">
                                <Link :href="`/tracks/${relatedTrack.id}`" class="block">
                                    <h3 class="font-semibold truncate">{{ relatedTrack.name }}</h3>
                                    <p class="text-sm text-muted-foreground truncate">
                                        {{ relatedTrack.artists.join(', ') }}
                                    </p>
                                </Link>
                            </div>
                            <div class="text-sm text-muted-foreground">
                                {{ formatDuration(relatedTrack.duration_ms) }}
                            </div>
                            <Button
                                variant="ghost"
                                size="sm"
                                @click="handleRemoveRelated(relatedTrack.id)"
                                class="text-destructive hover:text-destructive hover:bg-destructive/10"
                            >
                                <Trash2 class="w-4 h-4" />
                            </Button>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-muted-foreground">
                        <p>No related tracks yet.</p>
                        <p class="text-sm mt-2">Import a track to add it as a related track.</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
