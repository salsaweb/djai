<script setup lang="ts">
    import { Head, Form, usePage, Link, router } from '@inertiajs/vue3';
    
    import { type BreadcrumbItem } from '@/types';

    import {
        Table,
        TableBody,
        TableCell,
        TableHead,
        TableHeader,
        TableRow,
    } from '@/components/ui/table';

    import {
        Sheet,
        SheetContent,
        SheetDescription,
        SheetHeader,
        SheetTitle,
        SheetFooter,
        SheetTrigger,
    } from '@/components/ui/sheet';

    import {
        Dialog,
        DialogContent,
        DialogDescription,
        DialogFooter,
        DialogHeader,
        DialogTitle,
        DialogTrigger,
    } from '@/components/ui/dialog';

    import { Alert, AlertDescription } from '@/components/ui/alert';

    import { importSpotifyTrack, importCsv } from '@/routes/tracks';
    
    import AppLayout from '@/layouts/AppLayout.vue';
    import { edit } from '@/routes/tracks';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import InputError from '@/components/InputError.vue';
    import { PlusIcon, XCircle, CheckCircle2, Eye, Trash2, X, Filter, ChevronDown } from 'lucide-vue-next';
    import { type Track, type Artist, type Album } from '@/types';
    import { ref, computed, watch } from 'vue';
    import {
        DropdownMenu,
        DropdownMenuContent,
        DropdownMenuItem,
        DropdownMenuLabel,
        DropdownMenuSeparator,
        DropdownMenuTrigger,
    } from '@/components/ui/dropdown-menu';
    import {
        Collapsible,
        CollapsibleContent,
        CollapsibleTrigger,
    } from '@/components/ui/collapsible';

    const props = defineProps<{
        tracks: Track[];
        artists?: Artist[];
        albums?: Album[];
        filterOptions?: {
            genres?: string[];
            parent_genres?: string[];
            keys?: string[];
            camelots?: string[];
            time_signatures?: string[];
        };
        filters?: {
            artist_id?: number | string;
            album_id?: number | string;
            bpm_min?: number | string;
            bpm_max?: number | string;
            energy_min?: number | string;
            energy_max?: number | string;
            popularity_min?: number | string;
            popularity_max?: number | string;
            genre?: string;
            parent_genre?: string;
            key?: string;
            camelot?: string;
            label?: string;
            dance_min?: number | string;
            dance_max?: number | string;
            acoustic_min?: number | string;
            acoustic_max?: number | string;
            instrumental_min?: number | string;
            instrumental_max?: number | string;
            valence_min?: number | string;
            valence_max?: number | string;
            speech_min?: number | string;
            speech_max?: number | string;
            live_min?: number | string;
            live_max?: number | string;
            loud_db_min?: number | string;
            loud_db_max?: number | string;
            time_signature?: string;
            isrc?: string;
        };
    }>();

    const page = usePage();
    const alert = ref<{
        type: 'success' | 'error' | null
        message: string
    }>({
        type: null,
        message: '',
    });
    const showAlert = ref(false);
    
    const breadcrumbItems: BreadcrumbItem[] = [
        {
            title: 'Tracks',
            href: edit().url,
        },
    ];

    const openImportTrack = ref(false);
    const openImportCsv = ref(false);
    const openAdvancedFilters = ref(false);
    const deleteDialogOpen = ref<Record<number, boolean>>({});
    const trackToDelete = ref<Track | null>(null);
    
    // Advanced filter form
    const advancedFilters = ref({
        bpm_min: props.filters?.bpm_min || '',
        bpm_max: props.filters?.bpm_max || '',
        energy_min: props.filters?.energy_min || '',
        energy_max: props.filters?.energy_max || '',
        popularity_min: props.filters?.popularity_min || '',
        popularity_max: props.filters?.popularity_max || '',
        genre: props.filters?.genre || '',
        parent_genre: props.filters?.parent_genre || '',
        key: props.filters?.key || '',
        camelot: props.filters?.camelot || '',
        label: props.filters?.label || '',
        dance_min: props.filters?.dance_min || '',
        dance_max: props.filters?.dance_max || '',
        acoustic_min: props.filters?.acoustic_min || '',
        acoustic_max: props.filters?.acoustic_max || '',
        instrumental_min: props.filters?.instrumental_min || '',
        instrumental_max: props.filters?.instrumental_max || '',
        valence_min: props.filters?.valence_min || '',
        valence_max: props.filters?.valence_max || '',
        speech_min: props.filters?.speech_min || '',
        speech_max: props.filters?.speech_max || '',
        live_min: props.filters?.live_min || '',
        live_max: props.filters?.live_max || '',
        loud_db_min: props.filters?.loud_db_min || '',
        loud_db_max: props.filters?.loud_db_max || '',
        time_signature: props.filters?.time_signature || '',
        isrc: props.filters?.isrc || '',
    });

    watch(
        () => {
            const flash = page.props.flash as { success?: string; error?: string } | undefined;
            return [flash?.success, flash?.error];
        },
        ([success, error]) => {
            if (success) {
            alert.value = { type: 'success', message: success }
            showAlert.value = true
            }

            if (error) {
            alert.value = { type: 'error', message: error }
            showAlert.value = true
            }

            if (success || error) {
            setTimeout(() => {
                showAlert.value = false
            }, 3000)
            }
        },
        { immediate: true }
    )

    const formatDuration = (ms: number | null): string => {
        if (!ms) return 'N/A';
        const seconds = Math.floor(ms / 1000);
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;
        return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
    };

    const openDeleteDialog = (track: Track) => {
        trackToDelete.value = track;
        deleteDialogOpen.value[track.id] = true;
    };

    const closeDeleteDialog = (trackId: number) => {
        deleteDialogOpen.value[trackId] = false;
        trackToDelete.value = null;
    };

    const handleDelete = (track: Track) => {
        router.delete(`/tracks/${track.id}`, {
            onSuccess: () => {
                closeDeleteDialog(track.id);
            },
        });
    };

    const applyFilter = (type: 'artist' | 'album', value: string | null) => {
        const params = new URLSearchParams();
        const filters = props.filters;
        
        if (type === 'artist' && value) {
            params.set('artist_id', value);
        } else if (filters && filters.artist_id) {
            params.set('artist_id', String(filters.artist_id));
        }
        
        if (type === 'album' && value) {
            params.set('album_id', value);
        } else if (filters && filters.album_id) {
            params.set('album_id', String(filters.album_id));
        }
        
        router.get(edit().url + (params.toString() ? '?' + params.toString() : ''), {}, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const clearFilters = () => {
        // Reset advanced filters
        Object.keys(advancedFilters.value).forEach(key => {
            advancedFilters.value[key as keyof typeof advancedFilters.value] = '';
        });
        
        router.get(edit().url, {}, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const getArtistName = (track: Track): string => {
        if (track.artists_relation && track.artists_relation.length > 0) {
            return track.artists_relation.map(a => a.name).join(', ');
        }
        return track.artists.join(', ');
    };

    const getAlbumName = (track: Track): string => {
        return track.album_relation?.name || track.album || 'N/A';
    };

    const hasActiveFilters = computed(() => {
        const basicFilters = !!(props.filters?.artist_id || props.filters?.album_id);
        const advancedFiltersActive = Object.values(advancedFilters.value).some(v => v !== '' && v !== null && v !== undefined);
        return basicFilters || advancedFiltersActive;
    });

    const applyAdvancedFilters = () => {
        const params = new URLSearchParams();
        
        // Add basic filters
        if (props.filters?.artist_id) {
            params.set('artist_id', String(props.filters.artist_id));
        }
        if (props.filters?.album_id) {
            params.set('album_id', String(props.filters.album_id));
        }
        
        // Add advanced filters
        Object.entries(advancedFilters.value).forEach(([key, value]) => {
            if (value !== '' && value !== null && value !== undefined) {
                params.set(key, String(value));
            }
        });
        
        router.get(edit().url + (params.toString() ? '?' + params.toString() : ''), {}, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const selectedArtistName = computed(() => {
        if (!props.filters?.artist_id || !props.artists) return 'Filter by Artist';
        const artist = props.artists.find(a => a.id === Number(props.filters!.artist_id));
        return artist?.name || 'Filter by Artist';
    });

    const selectedAlbumName = computed(() => {
        if (!props.filters?.album_id || !props.albums) return 'Filter by Album';
        const album = props.albums.find(a => a.id === Number(props.filters!.album_id));
        return album?.name || 'Filter by Album';
    });
    
    </script>
    
    <template>
        <AppLayout :breadcrumbs="breadcrumbItems">
            <Head title="Tracks" />

            <h1 class="sr-only">Tracks</h1>

            <!-- Flash Messages -->
            <Transition name="fade">
                <div v-if="showAlert" class="mt-4">
                    <Alert :variant="alert.type === 'error' ? 'destructive' : 'default'">
                    <component
                        :is="alert.type === 'error' ? XCircle : CheckCircle2"
                        class="h-4 w-4"
                    />
                    <AlertDescription>
                        {{ alert.message }}
                    </AlertDescription>
                    </Alert>
                </div>
            </Transition>

            <div class="flex justify-between items-center mt-4 gap-4">
                <!-- Filters -->
                <div class="flex gap-4 items-center flex-1">
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" class="w-[200px] justify-start">
                                <Filter class="w-4 h-4 mr-2" />
                                {{ selectedArtistName }}
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent class="w-[200px]">
                            <DropdownMenuLabel>Artists</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem @click="applyFilter('artist', null)">
                                All Artists
                            </DropdownMenuItem>
                            <DropdownMenuItem 
                                v-for="artist in props.artists" 
                                :key="artist.id"
                                @click="applyFilter('artist', String(artist.id))"
                            >
                                {{ artist.name }}
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>

                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" class="w-[200px] justify-start">
                                <Filter class="w-4 h-4 mr-2" />
                                {{ selectedAlbumName }}
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent class="w-[200px]">
                            <DropdownMenuLabel>Albums</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem @click="applyFilter('album', null)">
                                All Albums
                            </DropdownMenuItem>
                            <DropdownMenuItem 
                                v-for="album in props.albums" 
                                :key="album.id"
                                @click="applyFilter('album', String(album.id))"
                            >
                                {{ album.name }}
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>

                    <Button 
                        v-if="hasActiveFilters"
                        variant="outline" 
                        size="sm"
                        @click="clearFilters"
                    >
                        <X class="w-4 h-4 mr-2" />
                        Clear Filters
                    </Button>
                </div>
            </div>

            <!-- Advanced Filters -->
            <Collapsible v-model:open="openAdvancedFilters" class="mt-4">
                <CollapsibleTrigger as-child>
                    <Button variant="outline" class="w-full justify-between">
                        <span class="flex items-center gap-2">
                            <Filter class="w-4 h-4" />
                            Advanced Filters
                        </span>
                        <ChevronDown class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': openAdvancedFilters }" />
                    </Button>
                </CollapsibleTrigger>
                <CollapsibleContent class="mt-4 p-4 border rounded-lg bg-muted/50">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- BPM Range -->
                        <div class="space-y-2">
                            <Label>BPM</Label>
                            <div class="flex gap-2">
                                <Input 
                                    v-model="advancedFilters.bpm_min" 
                                    type="number" 
                                    placeholder="Min" 
                                    class="w-full"
                                />
                                <Input 
                                    v-model="advancedFilters.bpm_max" 
                                    type="number" 
                                    placeholder="Max" 
                                    class="w-full"
                                />
                            </div>
                        </div>

                        <!-- Energy Range -->
                        <div class="space-y-2">
                            <Label>Energy</Label>
                            <div class="flex gap-2">
                                <Input 
                                    v-model="advancedFilters.energy_min" 
                                    type="number" 
                                    placeholder="Min" 
                                    class="w-full"
                                />
                                <Input 
                                    v-model="advancedFilters.energy_max" 
                                    type="number" 
                                    placeholder="Max" 
                                    class="w-full"
                                />
                            </div>
                        </div>

                        <!-- Popularity Range -->
                        <div class="space-y-2">
                            <Label>Popularity</Label>
                            <div class="flex gap-2">
                                <Input 
                                    v-model="advancedFilters.popularity_min" 
                                    type="number" 
                                    placeholder="Min" 
                                    class="w-full"
                                />
                                <Input 
                                    v-model="advancedFilters.popularity_max" 
                                    type="number" 
                                    placeholder="Max" 
                                    class="w-full"
                                />
                            </div>
                        </div>

                        <!-- Genre -->
                        <div class="space-y-2">
                            <Label>Genre</Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="w-full justify-start">
                                        {{ advancedFilters.genre || 'Select Genre' }}
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent>
                                    <DropdownMenuItem @click="advancedFilters.genre = ''">
                                        All Genres
                                    </DropdownMenuItem>
                                    <DropdownMenuItem 
                                        v-for="genre in props.filterOptions?.genres" 
                                        :key="genre"
                                        @click="advancedFilters.genre = genre"
                                    >
                                        {{ genre }}
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>

                        <!-- Parent Genre -->
                        <div class="space-y-2">
                            <Label>Parent Genre</Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="w-full justify-start">
                                        {{ advancedFilters.parent_genre || 'Select Parent Genre' }}
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent>
                                    <DropdownMenuItem @click="advancedFilters.parent_genre = ''">
                                        All Parent Genres
                                    </DropdownMenuItem>
                                    <DropdownMenuItem 
                                        v-for="genre in props.filterOptions?.parent_genres" 
                                        :key="genre"
                                        @click="advancedFilters.parent_genre = genre"
                                    >
                                        {{ genre }}
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>

                        <!-- Key -->
                        <div class="space-y-2">
                            <Label>Key</Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="w-full justify-start">
                                        {{ advancedFilters.key || 'Select Key' }}
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent>
                                    <DropdownMenuItem @click="advancedFilters.key = ''">
                                        All Keys
                                    </DropdownMenuItem>
                                    <DropdownMenuItem 
                                        v-for="key in props.filterOptions?.keys" 
                                        :key="key"
                                        @click="advancedFilters.key = key"
                                    >
                                        {{ key }}
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>

                        <!-- Camelot -->
                        <div class="space-y-2">
                            <Label>Camelot</Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="w-full justify-start">
                                        {{ advancedFilters.camelot || 'Select Camelot' }}
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent>
                                    <DropdownMenuItem @click="advancedFilters.camelot = ''">
                                        All Camelots
                                    </DropdownMenuItem>
                                    <DropdownMenuItem 
                                        v-for="camelot in props.filterOptions?.camelots" 
                                        :key="camelot"
                                        @click="advancedFilters.camelot = camelot"
                                    >
                                        {{ camelot }}
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>

                        <!-- Time Signature -->
                        <div class="space-y-2">
                            <Label>Time Signature</Label>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="outline" class="w-full justify-start">
                                        {{ advancedFilters.time_signature || 'Select Time Signature' }}
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent>
                                    <DropdownMenuItem @click="advancedFilters.time_signature = ''">
                                        All Time Signatures
                                    </DropdownMenuItem>
                                    <DropdownMenuItem 
                                        v-for="ts in props.filterOptions?.time_signatures" 
                                        :key="ts"
                                        @click="advancedFilters.time_signature = ts"
                                    >
                                        {{ ts }}
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>

                        <!-- Label -->
                        <div class="space-y-2">
                            <Label>Label</Label>
                            <Input 
                                v-model="advancedFilters.label" 
                                type="text" 
                                placeholder="Search label..." 
                            />
                        </div>

                        <!-- ISRC -->
                        <div class="space-y-2">
                            <Label>ISRC</Label>
                            <Input 
                                v-model="advancedFilters.isrc" 
                                type="text" 
                                placeholder="Search ISRC..." 
                            />
                        </div>

                        <!-- Dance Range -->
                        <div class="space-y-2">
                            <Label>Dance</Label>
                            <div class="flex gap-2">
                                <Input 
                                    v-model="advancedFilters.dance_min" 
                                    type="number" 
                                    placeholder="Min" 
                                    class="w-full"
                                />
                                <Input 
                                    v-model="advancedFilters.dance_max" 
                                    type="number" 
                                    placeholder="Max" 
                                    class="w-full"
                                />
                            </div>
                        </div>

                        <!-- Acoustic Range -->
                        <div class="space-y-2">
                            <Label>Acoustic</Label>
                            <div class="flex gap-2">
                                <Input 
                                    v-model="advancedFilters.acoustic_min" 
                                    type="number" 
                                    placeholder="Min" 
                                    class="w-full"
                                />
                                <Input 
                                    v-model="advancedFilters.acoustic_max" 
                                    type="number" 
                                    placeholder="Max" 
                                    class="w-full"
                                />
                            </div>
                        </div>

                        <!-- Instrumental Range -->
                        <div class="space-y-2">
                            <Label>Instrumental</Label>
                            <div class="flex gap-2">
                                <Input 
                                    v-model="advancedFilters.instrumental_min" 
                                    type="number" 
                                    placeholder="Min" 
                                    class="w-full"
                                />
                                <Input 
                                    v-model="advancedFilters.instrumental_max" 
                                    type="number" 
                                    placeholder="Max" 
                                    class="w-full"
                                />
                            </div>
                        </div>

                        <!-- Valence Range -->
                        <div class="space-y-2">
                            <Label>Valence</Label>
                            <div class="flex gap-2">
                                <Input 
                                    v-model="advancedFilters.valence_min" 
                                    type="number" 
                                    placeholder="Min" 
                                    class="w-full"
                                />
                                <Input 
                                    v-model="advancedFilters.valence_max" 
                                    type="number" 
                                    placeholder="Max" 
                                    class="w-full"
                                />
                            </div>
                        </div>

                        <!-- Speech Range -->
                        <div class="space-y-2">
                            <Label>Speech</Label>
                            <div class="flex gap-2">
                                <Input 
                                    v-model="advancedFilters.speech_min" 
                                    type="number" 
                                    placeholder="Min" 
                                    class="w-full"
                                />
                                <Input 
                                    v-model="advancedFilters.speech_max" 
                                    type="number" 
                                    placeholder="Max" 
                                    class="w-full"
                                />
                            </div>
                        </div>

                        <!-- Live Range -->
                        <div class="space-y-2">
                            <Label>Live</Label>
                            <div class="flex gap-2">
                                <Input 
                                    v-model="advancedFilters.live_min" 
                                    type="number" 
                                    placeholder="Min" 
                                    class="w-full"
                                />
                                <Input 
                                    v-model="advancedFilters.live_max" 
                                    type="number" 
                                    placeholder="Max" 
                                    class="w-full"
                                />
                            </div>
                        </div>

                        <!-- Loud (Db) Range -->
                        <div class="space-y-2">
                            <Label>Loud (Db)</Label>
                            <div class="flex gap-2">
                                <Input 
                                    v-model="advancedFilters.loud_db_min" 
                                    type="number" 
                                    step="0.01"
                                    placeholder="Min" 
                                    class="w-full"
                                />
                                <Input 
                                    v-model="advancedFilters.loud_db_max" 
                                    type="number" 
                                    step="0.01"
                                    placeholder="Max" 
                                    class="w-full"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 mt-4">
                        <Button variant="outline" @click="clearFilters">
                            Clear All
                        </Button>
                        <Button @click="applyAdvancedFilters">
                            Apply Filters
                        </Button>
                    </div>
                </CollapsibleContent>
            </Collapsible>

                <div class="flex gap-2">
                    <Sheet v-model:open="openImportTrack">
                        <SheetTrigger as-child>
                            <Button variant="outline">
                                <PlusIcon class="w-4 h-4" />
                                Import Track
                            </Button>
                        </SheetTrigger>
                    <SheetContent>
                        <SheetHeader>
                            <SheetTitle>Import New Track</SheetTitle>
                            <SheetDescription>
                                Import a new track to your library from Spotify.
                            </SheetDescription>
                        </SheetHeader>
                        <Form
                            v-bind="importSpotifyTrack.form()"
                            @success="openImportTrack = false"
                            class="space-y-6"
                            v-slot="{ errors, processing }"
                        >
                            <div class="grid flex-1 auto-rows-min gap-6 px-4">
                                <div class="grid gap-3">
                                    <Label for="url">Spotify URL</Label>
                                    <Input 
                                        name="url" 
                                        id="url" 
                                        type="text" 
                                        placeholder="https://open.spotify.com/track/1234567890"
                                        :class="{ 'border-red-500': errors.url }"
                                    />
                                    <InputError :message="errors.url" />
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
                                    <Button type="submit" :disabled="processing" class="w-[45%]">
                                        {{ processing ? 'Importing...' : 'Import Track' }}
                                    </Button>
                                </div>
                            </SheetFooter>
                        </Form>
                    </SheetContent>
                </Sheet>

                <Sheet v-model:open="openImportCsv">
                    <SheetTrigger as-child>
                        <Button variant="outline">
                            <PlusIcon class="w-4 h-4" />
                            Import CSV
                        </Button>
                    </SheetTrigger>
                    <SheetContent>
                        <SheetHeader>
                            <SheetTitle>Import CSV Metadata</SheetTitle>
                            <SheetDescription>
                                Upload a CSV file to update existing tracks with metadata (BPM, Energy, Genres, etc.).
                            </SheetDescription>
                        </SheetHeader>
                        <Form
                            v-bind="importCsv.form()"
                            @success="openImportCsv = false"
                            class="space-y-6"
                            v-slot="{ errors, processing }"
                            :enctype="'multipart/form-data'"
                        >
                            <div class="grid flex-1 auto-rows-min gap-6 px-4">
                                <div class="grid gap-3">
                                    <Label for="csv_file">CSV File</Label>
                                    <Input 
                                        name="csv_file" 
                                        id="csv_file" 
                                        type="file" 
                                        accept=".csv,.txt"
                                        :class="{ 'border-red-500': errors.csv_file }"
                                    />
                                    <InputError :message="errors.csv_file" />
                                    <p class="text-sm text-muted-foreground">
                                        The CSV file should contain tracks with Spotify Track Id column to match existing tracks.
                                    </p>
                                </div>
                            </div> 
                            
                            <SheetFooter>
                                <div class="w-full p-4 flex justify-between">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        class="w-[45%] mr-4"
                                        @click="openImportCsv = false"
                                    >
                                        Cancel
                                    </Button>
                                    <Button type="submit" :disabled="processing" class="w-[45%]">
                                        {{ processing ? 'Importing...' : 'Import CSV' }}
                                    </Button>
                                </div>
                            </SheetFooter>
                        </Form>
                    </SheetContent>
                </Sheet>
            </div>
            

            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-[100px]">Cover</TableHead>
                        <TableHead class="w-[100px]">Track</TableHead>
                        <TableHead>Artist</TableHead>
                        <TableHead>Album</TableHead>
                        <TableHead class="text-right">Duration</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="track in tracks" :key="track.id">
                        <TableCell class="font-medium">
                            <Link :href="`/tracks/${track.id}`">
                                <img
                                    v-if="track.album_art_url"
                                    :src="track.album_art_url"
                                    :alt="track.album || track.name"
                                    class="w-16 h-16 rounded object-cover"
                                />
                            </Link>
                        </TableCell>
                        <TableCell class="font-medium">{{ track.name }}</TableCell>
                        <TableCell>
                            <div class="flex flex-wrap gap-1">
                                <template v-if="track.artists_relation && track.artists_relation.length > 0">
                                    <button
                                        v-for="(artist, index) in track.artists_relation"
                                        :key="artist.id"
                                        @click="applyFilter('artist', String(artist.id))"
                                        class="text-primary hover:underline"
                                    >
                                        {{ artist.name }}<span v-if="index < track.artists_relation.length - 1">, </span>
                                    </button>
                                </template>
                                <span v-else>{{ track.artists.join(', ') }}</span>
                            </div>
                        </TableCell>
                        <TableCell>
                            <button
                                v-if="track.album_relation"
                                @click="applyFilter('album', String(track.album_relation.id))"
                                class="text-primary hover:underline"
                            >
                                {{ track.album_relation.name }}
                            </button>
                            <span v-else>{{ track.album || 'N/A' }}</span>
                        </TableCell>
                        <TableCell class="text-right">{{ formatDuration(track.duration_ms) }}</TableCell>
                        <TableCell class="text-right">
                            <div class="flex justify-end gap-2">
                                <Link :href="`/tracks/${track.id}`">
                                    <Button variant="ghost" size="sm">
                                        <Eye class="w-4 h-4" />
                                    </Button>
                                </Link>
                                <Dialog 
                                    v-model:open="deleteDialogOpen[track.id]"
                                >
                                    <DialogTrigger as-child>
                                        <Button 
                                            variant="ghost" 
                                            size="sm"
                                            @click="openDeleteDialog(track)"
                                        >
                                            <Trash2 class="w-4 h-4 text-destructive" />
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
                                            <Button 
                                                variant="secondary" 
                                                @click="closeDeleteDialog(track.id)"
                                            >
                                                Cancel
                                            </Button>
                                            <Button 
                                                variant="destructive" 
                                                @click="handleDelete(track)"
                                            >
                                                Delete Track
                                            </Button>
                                        </DialogFooter>
                                    </DialogContent>
                                </Dialog>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </AppLayout>
    </template>
    
    