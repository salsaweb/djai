<script setup lang="ts">
    import { Head, Form, usePage, Link, router } from '@inertiajs/vue3';
    
    import { type BreadcrumbItem } from '@/types';

    import {
        Table,
        TableBody,
        TableCaption,
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

    import { importSpotifyTrack } from '@/routes/tracks';
    
    import AppLayout from '@/layouts/AppLayout.vue';
    import { edit } from '@/routes/tracks';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import InputError from '@/components/InputError.vue';
    import { PlusIcon, XCircle, CheckCircle2, AlertCircle, Eye, Trash2 } from 'lucide-vue-next';
    import { type Track } from '@/types';
    import { ref, computed, watch } from 'vue';

    const props = defineProps<{
        tracks: Track[];
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
    const deleteDialogOpen = ref<Record<number, boolean>>({});
    const trackToDelete = ref<Track | null>(null);

    watch(
        () => [page.props.flash?.success, page.props.flash?.error],
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

            <div class="flex justify-end mt-4">
                <Sheet v-model:open="openImportTrack">
                    <SheetTrigger as-child>
                        <div class="flex justify-end mt-4 mr-2"> 
                            <Button variant="outline">
                                <PlusIcon class="w-4 h-4" />
                                Import Track
                            </Button>
                        </div>
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
            </div>
            

            <Table>
                <TableCaption>A list of your uploaded tracks.</TableCaption>
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
                        <TableCell>{{ track.artists.join(', ') }}</TableCell>
                        <TableCell>{{ track.album || 'N/A' }}</TableCell>
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
    
    