<script setup>
import { ref } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';


const showingNavigationDropdown = ref(false);
const sidebarOpen = ref(true);
</script>

<template>
  <div class="flex h-screen bg-green-50">
    <!-- Sidebar -->
    <aside
      :class="sidebarOpen ? 'w-64' : 'w-16'"
      class="bg-white border-r border-green-200 transition-all duration-300 flex flex-col shadow-lg"
    >
      <div class="flex items-center justify-center h-16 border-b border-green-200 bg-green-50">
        <ApplicationLogo class="h-10 w-auto" />
      </div>
      
      <nav class="flex-1 px-2 py-4 space-y-1">
        <Link
          href="/dashboard"
          class="flex items-center px-3 py-2.5 rounded-lg text-green-800 hover:bg-green-100 transition-colors duration-200 group"
          :class="{ 'bg-green-100 text-green-700 font-medium': route().current('dashboard') }"
        >
          <svg class="w-5 h-5 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          <span v-if="sidebarOpen">Dashboard</span>
        </Link>

        <Link
          href="/tickets"
          class="flex items-center px-3 py-2.5 rounded-lg text-green-800 hover:bg-green-100 transition-colors duration-200 group"
        >
          <svg class="w-5 h-5 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
          </svg>
          <span v-if="sidebarOpen">Tickets</span>
        </Link>

        <Link
          href="/ticket-comments"
          class="flex items-center px-3 py-2.5 rounded-lg text-green-800 hover:bg-green-100 transition-colors duration-200 group"
        >
          <svg class="w-5 h-5 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
          </svg>
          <span v-if="sidebarOpen">Comments</span>
        </Link>

        <Link
          href="/ticket-tags"
          class="flex items-center px-3 py-2.5 rounded-lg text-green-800 hover:bg-green-100 transition-colors duration-200 group"
        >
          <svg class="w-5 h-5 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l5 5a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-5-5A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
          </svg>
          <span v-if="sidebarOpen">Tags</span>
        </Link>

        <Link
          href="/ticket-history"
          class="flex items-center px-3 py-2.5 rounded-lg text-green-800 hover:bg-green-100 transition-colors duration-200 group"
        >
          <svg class="w-5 h-5 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span v-if="sidebarOpen">History</span>
        </Link>
      </nav>

      <div class="p-4 border-t border-green-200">
        <button
          @click="sidebarOpen = !sidebarOpen"
          class="w-full flex items-center justify-center px-3 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors duration-200 text-sm font-medium"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="sidebarOpen ? 'M11 19l-7-7 7-7m8 14l-7-7 7-7' : 'M13 5l7 7-7 7M5 5l7 7-7 7'" />
          </svg>
          <span>{{ sidebarOpen ? 'Collapse' : 'Expand' }}</span>
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col">
      <!-- Top Navigation -->
      <nav class="border-b border-green-200 bg-white shadow-sm">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div class="flex h-16 justify-between">
            <div class="flex">
              <!-- Primary Navigation -->
              <div class="hidden space-x-8 sm:flex sm:ms-10">
                <NavLink 
                  :href="route('dashboard')" 
                  :active="route().current('dashboard')"
                  class="text-green-700 hover:text-green-900"
                >
                  Dashboard
                </NavLink>
              </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
              <div class="relative ms-3">
                <Dropdown align="right" width="48">
                  <template #trigger>
                    <span class="inline-flex rounded-md">
                      <button
                        type="button"
                        class="inline-flex items-center rounded-md border border-green-200 bg-white px-3 py-2 text-sm font-medium text-green-700 hover:bg-green-50 hover:text-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors duration-200"
                      >
                        {{ $page.props.auth.user.name }}
                        <svg class="-me-0.5 ms-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                          <path
                            fill-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd"
                          />
                        </svg>
                      </button>
                    </span>
                  </template>

                  <template #content>
                    <div class="bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5">
                      <DropdownLink :href="route('profile.edit')" class="text-green-700 hover:bg-green-50">Profile</DropdownLink>
                      <DropdownLink :href="route('logout')" method="post" as="button" class="text-green-700 hover:bg-green-50">Log Out</DropdownLink>
                    </div>
                  </template>
                </Dropdown>
              </div>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
              <button
                @click="showingNavigationDropdown = !showingNavigationDropdown"
                class="inline-flex items-center justify-center rounded-md p-2 text-green-600 hover:bg-green-100 hover:text-green-900 focus:outline-none focus:ring-2 focus:ring-green-500"
              >
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                  <path
                    :class="{ hidden: showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16"
                  />
                  <path
                    :class="{ hidden: !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Responsive Navigation Menu -->
        <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden border-t border-green-200 bg-white">
          <div class="space-y-1 pb-3 pt-2">
            <ResponsiveNavLink 
              :href="route('dashboard')" 
              :active="route().current('dashboard')"
              class="text-green-700 hover:bg-green-50 hover:text-green-900"
            >
              Dashboard
            </ResponsiveNavLink>
          </div>

          <div class="border-t border-green-200 pb-1 pt-4">
            <div class="px-4 text-base font-medium text-green-800">{{ $page.props.auth.user.name }}</div>
            <div class="px-4 text-sm font-medium text-green-600">{{ $page.props.auth.user.email }}</div>
            <div class="mt-3 space-y-1">
              <ResponsiveNavLink 
                :href="route('profile.edit')"
                class="text-green-700 hover:bg-green-50"
              >
                Profile
              </ResponsiveNavLink>
              <ResponsiveNavLink 
                :href="route('logout')" 
                method="post" 
                as="button"
                class="text-green-700 hover:bg-green-50"
              >
                Log Out
              </ResponsiveNavLink>
            </div>
          </div>
        </div>
      </nav>

      <!-- Page Heading -->
      <header class="bg-white shadow-sm border-b border-green-200" v-if="$slots.header">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
          <slot name="header" />
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 overflow-auto p-6 bg-green-50">
        <div class="bg-white rounded-lg shadow-sm p-6 border border-green-100">
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>

<style scoped>
/* Smooth transitions for sidebar */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}

/* Custom scrollbar for webkit browsers */
.webkit-scrollbar {
  width: 8px;
  height: 8px;
}

.webkit-scrollbar-track {
  background: #f0f7e8;
}

.webkit-scrollbar-thumb {
  background: #9bbb5e;
  border-radius: 4px;
}

.webkit-scrollbar-thumb:hover {
  background: #7fa545;
}
</style>