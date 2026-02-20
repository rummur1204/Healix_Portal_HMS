<template>
    <div class="min-h-screen bg-gradient-to-br from-primary-50 to-teal-50 dark:from-primary-950 dark:to-teal-950">
        <!-- Sidebar with event binding -->
        <Sidebar @update:collapsed="isSidebarCollapsed = $event" />

        <!-- Main Content - adjusts based on sidebar state -->
        <div :class="['transition-all duration-300', isSidebarCollapsed ? 'ml-20' : 'ml-64']">
            <!-- Top Bar -->
            <header class="bg-white/80 dark:bg-primary-900/80 backdrop-blur-xl border-b border-primary-200 dark:border-primary-800 sticky top-0 z-30 shadow-sm">
                <div class="px-6 py-4">
                    <!-- First Row: Page Title with Icon and Right Actions -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <!-- Page Icon -->
                            <div class="p-2.5 bg-gradient-to-br from-primary-500 to-teal-500 rounded-xl shadow-md">
                                <component :is="currentPageIcon" class="w-5 h-5 text-white" />
                            </div>
                            <!-- Page Title -->
                            <h1 class="text-2xl font-bold text-primary-900 dark:text-white">
                                {{ currentPageTitle }}
                            </h1>
                        </div>

                        <!-- Right Side Actions -->
                        <div class="flex items-center space-x-4">
                            <!-- Search (hidden on mobile) -->
                            <div class="relative hidden md:block">
                                <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-primary-400" />
                                <input
                                    type="text"
                                    placeholder="Search..."
                                    class="pl-10 pr-4 py-2.5 w-64 rounded-xl border border-primary-200 dark:border-primary-700 bg-white dark:bg-primary-800/50 focus:bg-white dark:focus:bg-primary-800 text-primary-900 dark:text-white placeholder-primary-400 dark:placeholder-primary-500 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all"
                                />
                            </div>

                            <!-- Theme Toggle -->
                            <ThemeToggle />

                            <!-- Notifications -->
                            <button class="relative p-2.5 rounded-xl hover:bg-primary-100 dark:hover:bg-primary-800 transition-colors">
                                <BellIcon class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                                <span class="absolute top-2 right-2 w-2 h-2 bg-teal-500 rounded-full ring-2 ring-white dark:ring-primary-900"></span>
                            </button>
                        </div>
                    </div>

                    <!-- Second Row: Welcome Message - ROLE REMOVED -->
                    <div class="mt-3">
                        <p class="text-base text-primary-600 dark:text-primary-400">
                            Welcome back, <span class="font-semibold text-primary-900 dark:text-white">{{ userName }}</span>
                        </p>
                    </div>
                </div>

                <!-- Breadcrumbs -->
                <div v-if="$slots.breadcrumbs" class="px-6 pb-3">
                    <slot name="breadcrumbs" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-6">
                <!-- Flash Messages -->
                <div v-if="flash?.success" class="mb-4 p-4 bg-green-100 dark:bg-green-900/50 border-l-4 border-green-500 text-green-800 dark:text-green-100 rounded-r-lg">
                    {{ flash.success }}
                </div>
                <div v-if="flash?.error" class="mb-4 p-4 bg-red-100 dark:bg-red-900/50 border-l-4 border-red-500 text-red-800 dark:text-red-100 rounded-r-lg">
                    {{ flash.error }}
                </div>
                <div v-if="flash?.warning" class="mb-4 p-4 bg-yellow-100 dark:bg-yellow-900/50 border-l-4 border-yellow-500 text-yellow-800 dark:text-yellow-100 rounded-r-lg">
                    {{ flash.warning }}
                </div>

                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import Sidebar from './Sidebar.vue'
import ThemeToggle from '@/Components/ThemeToggle.vue'
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
} from '@heroicons/vue/24/outline'

const page = usePage()

// Track sidebar state
const isSidebarCollapsed = ref(false)

// Safe access to flash messages
const flash = computed(() => page.props?.flash || {})

// Get current user info - only need name now
const user = computed(() => page.props?.auth?.user || {})
const userName = computed(() => user.value?.name || 'User')

// Get current route path
const currentPath = computed(() => window.location.pathname)

// Page titles and icons based on route
const pageConfig = computed(() => {
    const path = currentPath.value
    
    if (path.includes('/dashboard')) {
        return {
            title: 'Dashboard',
            icon: HomeIcon
        }
    } else if (path.includes('/clients')) {
        return {
            title: 'Clients',
            icon: UserGroupIcon
        }
    } else if (path.includes('/subscriptions')) {
        return {
            title: 'Subscriptions',
            icon: CurrencyDollarIcon
        }
    } else if (path.includes('/tickets')) {
        return {
            title: 'Tickets',
            icon: TicketIcon
        }
    } else if (path.includes('/versions')) {
        return {
            title: 'Versions',
            icon: CubeIcon
        }
    } else if (path.includes('/reports')) {
        return {
            title: 'Reports',
            icon: ChartBarIcon
        }
    } else if (path.includes('/communications')) {
        return {
            title: 'Communications',
            icon: EnvelopeIcon
        }
    } else if (path.includes('/settings')) {
        return {
            title: 'Settings',
            icon: Cog6ToothIcon
        }
    } else {
        return {
            title: 'Healix-Plus',
            icon: HomeIcon
        }
    }
})

const currentPageTitle = computed(() => pageConfig.value.title)
const currentPageIcon = computed(() => pageConfig.value.icon)
</script>