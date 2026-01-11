<script setup lang="ts">
import { dashboard, login, register } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div
        class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]"
    >
        <div class="absolute inset-0">
            <img src="img/unsplash.jpg" alt="" class="w-full h-full object-cover"/>
            <div class="absolute inset-0 bg-gradient-to-r from-black/85 to-black/75" />
        </div>
        <header
            class="z-10 mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-4xl"
        >
            <nav class="flex items-center justify-end gap-4">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                >
                    Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                    >
                        Log in
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="register()"
                        class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                    >
                        Register
                    </Link>
                </template>
            </nav>
        </header>

        <!-- Hero Section -->
        <section class="z-10 flex flex-col items-center justify-center text-center py-10 px-6">
            <h2 class="text-4xl md:text-5xl font-extrabold mb-6 text-white">Smarter Playlists for Smarter DJs</h2>
            <p class="text-lg md:text-xl text-gray-300 max-w-2xl mb-5 leading-relaxed">
                DJAI helps DJs build smarter playlists automatically using track metadata like BPM, key, and genre.
                Designed for both live sets and playlist creation, it evolves with your style through AI-driven recommendations.
            </p>
        </section>

        <!-- Three Content Blocks -->
        <section class="z-10 grid grid-cols-1 md:grid-cols-3 gap-10 px-8 md:px-10 py-7 border-t border-gray-800">
            <!-- Block 1 -->
            <div class="text-center">
                <div class="text-indigo-500 text-5xl mb-4">🎧</div>
                <h3 class="text-2xl font-semibold text-white mb-3">Smart Track Selection</h3>
                <p class="text-gray-400 leading-relaxed">
                Automatically curate playlists using track metadata such as BPM, key, genre, and vibe for seamless transitions.
                </p>
            </div>

            <!-- Block 2 -->
            <div class="text-center">
                <div class="text-indigo-500 text-5xl mb-4">⚙️</div>
                <h3 class="text-2xl font-semibold text-white mb-3">Adaptive AI Learning</h3>
                <p class="text-gray-400 leading-relaxed">
                DJAI learns your style over time, refining track recommendations to match your energy and mixing preferences.
                </p>
            </div>

            <!-- Block 3 -->
            <div class="text-center">
                <div class="text-indigo-500 text-5xl mb-4">🌐</div>
                <h3 class="text-2xl font-semibold text-white mb-3">Multi-Platform Integration</h3>
                <p class="text-gray-400 leading-relaxed">
                Export and sync your playlists effortlessly to Spotify, Apple Music, and Google Music for easy performance access.
                </p>
            </div>
        </section>


        
        <div class="hidden h-14.5 lg:block"></div>
    </div>
</template>
