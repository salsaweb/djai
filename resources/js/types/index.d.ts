import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface Artist {
    id: number;
    name: string;
    spotify_id: string | null;
    created_at: string;
    updated_at: string;
}

export interface Album {
    id: number;
    name: string;
    spotify_id: string | null;
    album_art_url: string | null;
    created_at: string;
    updated_at: string;
}

export interface Track {
    id: number;
    user_id: number;
    album_id: number | null;
    spotify_id: string;
    name: string;
    artists: string[];
    album: string | null;
    album_art_url: string | null;
    duration_ms: number | null;
    spotify_url: string;
    preview_url: string | null;
    created_at: string;
    updated_at: string;
    related_tracks?: Track[];
    artists_relation?: Artist[];
    album_relation?: Album;
    bpm?: number | null;
    camelot?: string | null;
    energy?: number | null;
    popularity?: number | null;
    genres?: string[] | null;
    parent_genres?: string[] | null;
    dance?: number | null;
    acoustic?: number | null;
    instrumental?: number | null;
    valence?: number | null;
    speech?: number | null;
    live?: number | null;
    loud_db?: number | null;
    key?: string | null;
    time_signature?: string | null;
    label?: string | null;
    isrc?: string | null;
}

export type BreadcrumbItemType = BreadcrumbItem;
