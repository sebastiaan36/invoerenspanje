<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, UserCircle } from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';

const page = usePage<{ portal?: { unreadMessages: number } | null }>();
const unreadMessages = computed(() => page.props.portal?.unreadMessages ?? 0);

// Multi-dossier: een klant kan meerdere dossiers hebben. Per-dossier nav
// (overzicht / documenten / berichten) leeft binnen de dossier-tabs zelf.
// De sidebar blijft account-breed.
const mainNavItems = computed<NavItem[]>(() => [
    {
        title: 'Mijn dossiers',
        href: '/portaal',
        icon: LayoutGrid,
        ...(unreadMessages.value > 0 ? { badge: String(unreadMessages.value) } : {}),
    },
    { title: 'Profiel', href: '/settings/profile', icon: UserCircle },
]);
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/portaal">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
