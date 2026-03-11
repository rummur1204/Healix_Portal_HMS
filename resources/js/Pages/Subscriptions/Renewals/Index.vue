<template>
    <AuthenticatedLayout>
        <template #breadcrumbs>
            <div class="flex items-center space-x-2 text-sm">
                <Link href="/dashboard" class="text-gray-500 hover:text-teal-600 dark:text-gray-400 dark:hover:text-teal-400">Dashboard</Link>
                <ChevronRightIcon class="w-4 h-4 text-gray-400" />
                <span class="text-gray-900 dark:text-white font-medium">Renewals</span>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Renewals Dashboard</h2>
                <button 
                    @click="exportRenewals"
                    class="btn-outline flex items-center space-x-2"
                >
                    <ArrowDownTrayIcon class="w-5 h-5" />
                    <span>Export</span>
                </button>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <StatsCard
                    title="Due Today"
                    :value="stats.due_today"
                    :icon="ClockIcon"
                    iconBgClass="bg-red-100 dark:bg-red-900/30"
                    iconColorClass="text-red-600 dark:text-red-400"
                />
                
                <StatsCard
                    title="Next 7 Days"
                    :value="stats.due_7_days"
                    :icon="CalendarIcon"
                    iconBgClass="bg-yellow-100 dark:bg-yellow-900/30"
                    iconColorClass="text-yellow-600 dark:text-yellow-400"
                />
                
                <StatsCard
                    title="Next 15 Days"
                    :value="stats.due_15_days"
                    :icon="CalendarIcon"
                    iconBgClass="bg-teal-100 dark:bg-teal-900/30"
                    iconColorClass="text-teal-600 dark:text-teal-400"
                />
                
                <StatsCard
                    title="Next 30 Days"
                    :value="stats.due_30_days"
                    :icon="CalendarIcon"
                    iconBgClass="bg-green-100 dark:bg-green-900/30"
                    iconColorClass="text-green-600 dark:text-green-400"
                />
            </div>

            <!-- Quick Access Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <Link 
                    href="/subscriptions/renewals/due/0"
                    class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-red-600 to-red-700 p-6 hover:shadow-lg transition-all"
                >
                    <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-12 translate-x-12"></div>
                    <div class="relative">
                        <h3 class="text-xl font-bold text-white mb-2">Due Today</h3>
                        <p class="text-3xl font-bold text-white mb-2">{{ stats.due_today }}</p>
                        <p class="text-red-100 text-sm">Renewals requiring immediate attention</p>
                        <div class="mt-4 flex items-center text-white/80 group-hover:text-white transition-colors">
                            <span>View Details</span>
                            <ArrowRightIcon class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" />
                        </div>
                    </div>
                </Link>

                <Link 
                    href="/subscriptions/renewals/due/7"
                    class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-yellow-600 to-yellow-700 p-6 hover:shadow-lg transition-all"
                >
                    <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-12 translate-x-12"></div>
                    <div class="relative">
                        <h3 class="text-xl font-bold text-white mb-2">Next 7 Days</h3>
                        <p class="text-3xl font-bold text-white mb-2">{{ stats.due_7_days }}</p>
                        <p class="text-yellow-100 text-sm">Upcoming renewals this week</p>
                        <div class="mt-4 flex items-center text-white/80 group-hover:text-white transition-colors">
                            <span>View Details</span>
                            <ArrowRightIcon class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" />
                        </div>
                    </div>
                </Link>

                <Link 
                    href="/subscriptions/renewals/due/30"
                    class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-teal-600 to-teal-700 p-6 hover:shadow-lg transition-all"
                >
                    <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -translate-y-12 translate-x-12"></div>
                    <div class="relative">
                        <h3 class="text-xl font-bold text-white mb-2">Next 30 Days</h3>
                        <p class="text-3xl font-bold text-white mb-2">{{ stats.due_30_days }}</p>
                        <p class="text-teal-100 text-sm">Monthly renewal forecast</p>
                        <div class="mt-4 flex items-center text-white/80 group-hover:text-white transition-colors">
                            <span>View Details</span>
                            <ArrowRightIcon class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" />
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Recent Renewals Table -->
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-800">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <ArrowPathIcon class="w-5 h-5 mr-2 text-teal-600" />
                        Recent Renewals
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Subscription ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Client</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Plan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Renewal Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            <tr v-for="renewal in recentRenewals" :key="renewal.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ renewal.subscription_id }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ renewal.client?.organization_name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ renewal.plan?.plan_name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ formatDate(renewal.renewal_date) }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">${{ renewal.plan?.price }}</td>
                                <td class="px-6 py-4">
                                    <SubscriptionStatusBadge :status="renewal.status" />
                                </td>
                                <td class="px-6 py-4">
                                    <Link 
                                        :href="`/subscriptions/subscription/${renewal.id}`"
                                        class="text-teal-600 hover:text-teal-800 dark:text-teal-400 dark:hover:text-teal-300"
                                    >
                                        View
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="!recentRenewals.length">
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                    No renewals found
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatsCard from '@/Components/StatsCard.vue'
import SubscriptionStatusBadge from '@/Components/Subscription/SubscriptionStatusBadge.vue'
import {
    ChevronRightIcon,
    ClockIcon,
    CalendarIcon,
    ArrowPathIcon,
    ArrowRightIcon,
    ArrowDownTrayIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
    stats: Object
})

const recentRenewals = ref([])

onMounted(async () => {
    try {
        const response = await fetch('/subscriptions/renewals/due/30')
        const data = await response.json()
        recentRenewals.value = data.slice(0, 5)
    } catch (error) {
        console.error('Failed to fetch renewals:', error)
    }
})

const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const exportRenewals = () => {
    window.location.href = '/subscriptions/reports/renewals/export'
}
</script>