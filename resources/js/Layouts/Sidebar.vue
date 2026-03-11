<template>
    <aside :class="[
        'fixed left-0 top-0 z-40 h-screen transition-all duration-300 flex flex-col',
        isCollapsed ? 'w-20' : 'w-64',
        'bg-primary-800 dark:bg-primary-900 border-r border-primary-700 dark:border-primary-700'
    ]">
        <!-- Logo Area - Fixed at top -->
        <div class="flex items-center justify-between h-24 px-4 border-b border-primary-700 dark:border-primary-700 flex-shrink-0">
            <Logo v-if="!isCollapsed" />
            <div v-else class="w-12 h-12 bg-gradient-to-br from-primary-600 to-teal-600 rounded-xl flex items-center justify-center mx-auto shadow-lg">
                <span class="text-white font-bold text-2xl">H+</span>
            </div>
            <button
                @click="toggleSidebar"
                class="p-2.5 rounded-xl hover:bg-primary-700 dark:hover:bg-primary-800 transition-colors"
            >
                <ChevronDoubleLeftIcon v-if="!isCollapsed" class="w-5 h-5 text-primary-200 dark:text-primary-200" />
                <ChevronDoubleRightIcon v-else class="w-5 h-5 text-primary-200 dark:text-primary-200" />
            </button>
        </div>

        <!-- Navigation - Scrollable area with LARGER TEXT -->
        <div class="flex-1 overflow-y-auto py-5 px-3 sidebar-scroll">
            <nav class="space-y-1.5">
                <div v-for="item in navigationItems" :key="item.name">
                    <Link
                        :href="item.href"
                        :class="[
                            'sidebar-link group flex items-center px-4 py-4 rounded-xl transition-all duration-200',
                            isActiveRoute(item.href)
                                ? 'bg-primary-700 dark:bg-primary-800 text-white shadow-md border-l-4 border-teal-400' 
                                : 'text-primary-100 dark:text-primary-200 hover:bg-primary-700/70 dark:hover:bg-primary-800/70 hover:text-white hover:border-l-4 hover:border-teal-400/50'
                        ]"
                    >
                        <component :is="item.icon" class="w-6 h-6 flex-shrink-0" :class="isCollapsed ? 'mx-auto' : 'mr-4'" :style="{ color: isActiveRoute(item.href) ? 'white' : 'rgba(255,255,255,0.95)' }" />
                        <span v-if="!isCollapsed" class="flex-1 text-base font-semibold tracking-wide">{{ item.name }}</span>
                    </Link>
                </div>
            </nav>
        </div>

        <!-- Logout Button Only - Fixed at bottom -->
        <div class="p-4 border-t border-primary-700 dark:border-primary-700 bg-primary-800 dark:bg-primary-900 flex-shrink-0">
            <button
                @click="logout"
                class="w-full flex items-center justify-center px-4 py-3.5 bg-primary-700 hover:bg-red-600 text-white font-medium rounded-xl transition-all duration-200 shadow-md group"
            >
                <ArrowRightOnRectangleIcon class="w-5 h-5 mr-3 group-hover:translate-x-1 transition-transform" />
                <span v-if="!isCollapsed" class="text-sm font-semibold">Logout</span>
            </button>
        </div>
    </aside>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import Logo from '@/Components/Logo.vue'
import {
    HomeIcon,
    UserGroupIcon,
    CurrencyDollarIcon,
    TicketIcon,
    CubeIcon,
    ChartBarIcon,
    EnvelopeIcon,
    Cog6ToothIcon,
    ChevronDoubleLeftIcon,
    ChevronDoubleRightIcon,
    ArrowRightOnRectangleIcon
} from '@heroicons/vue/24/outline'

// Get the page object from Inertia
const page = usePage()

// State
const isCollapsed = ref(false)

// Emit event to parent (AuthenticatedLayout)
const emit = defineEmits(['update:collapsed'])

// Toggle sidebar and emit state
const toggleSidebar = () => {
    isCollapsed.value = !isCollapsed.value
    emit('update:collapsed', isCollapsed.value)
}

// Helper function to check if route is active
const isActiveRoute = (href) => {
    // Special case for subscriptions - match both /subscriptions and /subscriptions/*
    if (href === '/subscriptions') {
        return page.url.startsWith('/subscriptions')
    }
    // For other routes, check exact match or starts with (for nested routes)
    return page.url === href || page.url.startsWith(href + '/')
}

// Navigation items - CORRECTED subscription link
const navigationItems = [
    { name: 'Dashboard', href: '/dashboard', icon: HomeIcon },
    { name: 'Clients', href: '/clients', icon: UserGroupIcon },
    { name: 'Subscriptions', href: '/subscriptions', icon: CurrencyDollarIcon }, // Changed from '/subscriptions/plans' to '/subscriptions'
    { name: 'Tickets', href: '/tickets', icon: TicketIcon },
    { name: 'Versions', href: '/versions', icon: CubeIcon },
    { name: 'Reports', href: '/reports', icon: ChartBarIcon },
    { name: 'Communications', href: '/communications', icon: EnvelopeIcon },
    { name: 'Settings', href: '/settings', icon: Cog6ToothIcon },
]

// Logout function
const logout = () => {
    router.post('/logout')
}
</script>

<style scoped>
/* Smooth transitions for hover effects */
.sidebar-link {
    transition: all 0.2s ease-in-out;
}

/* Custom scrollbar for sidebar */
.sidebar-scroll {
    scrollbar-width: thin;
    scrollbar-color: #2dd4bf #115e59;
}

.sidebar-scroll::-webkit-scrollbar {
    width: 4px;
}

.sidebar-scroll::-webkit-scrollbar-track {
    background: #1e3a2f;
    border-radius: 2px;
}

.sidebar-scroll::-webkit-scrollbar-thumb {
    background: #2dd4bf;
    border-radius: 2px;
}

.sidebar-scroll::-webkit-scrollbar-thumb:hover {
    background: #5eead4;
}

/* Ensure text is always visible */
aside {
    color-scheme: dark;
}

/* Ensure content can scroll properly */
.flex-1 {
    min-height: 0;
    height: 100%;
}

/* Fixed width maintained */
.w-64 {
    width: 16rem; /* 256px - standard width */
}

.w-20 {
    width: 5rem; /* 80px - standard collapsed width */
}

/* Logout button hover effect */
.group:hover svg {
    transform: translateX(2px);
}
</style>