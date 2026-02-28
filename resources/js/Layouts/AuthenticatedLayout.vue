<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Sidebar from './Sidebar.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';
import {
    MagnifyingGlassIcon,
    BellIcon,
    HomeIcon,
    UserGroupIcon,
    CurrencyDollarIcon,
    TicketIcon,
    CubeIcon,
    ChartBarIcon,
    EnvelopeIcon,
    Cog6ToothIcon
} from '@heroicons/vue/24/outline';

const page = usePage();

// Sidebar state
const isSidebarCollapsed = ref(false);
const sidebarOpen = ref(true);
const showingNavigationDropdown = ref(false);

// Flash messages
const flash = computed(() => page.props?.flash || {});

// Current user info
const user = computed(() => page.props?.auth?.user || {});
const userName = computed(() => user.value?.name || 'User');

// Current path for page titles
const currentPath = computed(() => window.location.pathname);

// Page titles and icons
const pageConfig = computed(() => {
    const path = currentPath.value;
    if (path.includes('/dashboard')) return { title: 'Dashboard', icon: HomeIcon };
    if (path.includes('/clients')) return { title: 'Clients', icon: UserGroupIcon };
    if (path.includes('/subscriptions')) return { title: 'Subscriptions', icon: CurrencyDollarIcon };
    if (path.includes('/tickets')) return { title: 'Tickets', icon: TicketIcon };
    if (path.includes('/versions')) return { title: 'Versions', icon: CubeIcon };
    if (path.includes('/reports')) return { title: 'Reports', icon: ChartBarIcon };
    if (path.includes('/communications')) return { title: 'Communications', icon: EnvelopeIcon };
    if (path.includes('/settings')) return { title: 'Settings', icon: Cog6ToothIcon };
    return { title: 'Healix-Plus', icon: HomeIcon };
});

const currentPageTitle = computed(() => pageConfig.value.title);
const currentPageIcon = computed(() => pageConfig.value.icon);
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-primary-50 to-teal-50 dark:from-primary-950 dark:to-teal-950 flex">
        <!-- Sidebar -->
        <Sidebar @update:collapsed="isSidebarCollapsed = $event" :class="sidebarOpen ? 'w-64' : 'w-16'" />

        <!-- Main Content -->
        <div :class="['flex-1 transition-all duration-300', isSidebarCollapsed ? 'ml-20' : 'ml-64']">
            <!-- Top Bar -->
            <header class="bg-white/80 dark:bg-primary-900/80 backdrop-blur-xl border-b border-primary-200 dark:border-primary-800 sticky top-0 z-30 shadow-sm">
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="p-2.5 bg-gradient-to-br from-primary-500 to-teal-500 rounded-xl shadow-md">
                                <component :is="currentPageIcon" class="w-5 h-5 text-white" />
                            </div>
                            <h1 class="text-2xl font-bold text-primary-900 dark:text-white">{{ currentPageTitle }}</h1>
                        </div>

                        <div class="flex items-center space-x-4">
                            <div class="relative hidden md:block">
                                <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-primary-400" />
                                <input type="text" placeholder="Search..." class="pl-10 pr-4 py-2.5 w-64 rounded-xl border border-primary-200 dark:border-primary-700 bg-white dark:bg-primary-800/50 focus:ring-2 focus:ring-teal-500 transition-all" />
                            </div>
                            <ThemeToggle />
                            <button class="relative p-2.5 rounded-xl hover:bg-primary-100 dark:hover:bg-primary-800 transition-colors">
                                <BellIcon class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                                <span class="absolute top-2 right-2 w-2 h-2 bg-teal-500 rounded-full ring-2 ring-white dark:ring-primary-900"></span>
                            </button>
                        </div>
                    </div>

                    <div class="mt-3">
                        <p class="text-base text-primary-600 dark:text-primary-400">
                            Welcome back, <span class="font-semibold text-primary-900 dark:text-white">{{ userName }}</span>
                        </p>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-auto p-6">
                <div v-if="flash?.success" class="mb-4 p-4 bg-green-100 dark:bg-green-900/50 border-l-4 border-green-500 text-green-800 dark:text-green-100 rounded-r-lg">{{ flash.success }}</div>
                <div v-if="flash?.error" class="mb-4 p-4 bg-red-100 dark:bg-red-900/50 border-l-4 border-red-500 text-red-800 dark:text-red-100 rounded-r-lg">{{ flash.error }}</div>
                <div v-if="flash?.warning" class="mb-4 p-4 bg-yellow-100 dark:bg-yellow-900/50 border-l-4 border-yellow-500 text-yellow-800 dark:text-yellow-100 rounded-r-lg">{{ flash.warning }}</div>

                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}
.webkit-scrollbar { width: 8px; height: 8px; }
.webkit-scrollbar-track { background: #f0f7e8; }
.webkit-scrollbar-thumb { background: #9bbb5e; border-radius: 4px; }
.webkit-scrollbar-thumb:hover { background: #7fa545; }
</style>