<template>
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800">
            <div class="bg-white dark:bg-gray-900 rounded-lg p-4 shadow-sm">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Renewals</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ renewals.length }}</p>
            </div>
            <div class="bg-white dark:bg-gray-900 rounded-lg p-4 shadow-sm">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Amount</p>
                <p class="text-2xl font-bold text-green-600">${{ totalAmount }}</p>
            </div>
            <div class="bg-white dark:bg-gray-900 rounded-lg p-4 shadow-sm">
                <p class="text-sm text-gray-500 dark:text-gray-400">Due This Week</p>
                <p class="text-2xl font-bold text-yellow-600">{{ dueThisWeek }}</p>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="table-header">Subscription ID</th>
                        <th class="table-header">Client</th>
                        <th class="table-header">Plan</th>
                        <th class="table-header">Renewal Date</th>
                        <th class="table-header">Days Left</th>
                        <th class="table-header">Amount</th>
                        <th class="table-header">Status</th>
                        <th class="table-header">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    <tr v-for="renewal in sortedRenewals" :key="renewal.id" 
                        class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                        :class="{ 'bg-red-50 dark:bg-red-900/10': isUrgent(renewal.renewal_date) }"
                    >
                        <td class="table-cell font-medium text-gray-900 dark:text-white">
                            <Link :href="`/subscriptions/subscription/${renewal.id}`" class="hover:text-teal-600">
                                {{ renewal.subscription_id }}
                            </Link>
                        </td>
                        <td class="table-cell">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-gradient-to-br from-teal-500 to-green-600 rounded-full flex items-center justify-center text-white text-sm font-medium mr-3">
                                    {{ renewal.client?.organization_name?.charAt(0) || '?' }}
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">
                                        {{ renewal.client?.organization_name }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ renewal.client?.email }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="table-cell">
                            <div class="font-medium">{{ renewal.plan?.plan_name }}</div>
                            <div class="text-xs text-gray-500">{{ renewal.plan?.billing_cycle }}</div>
                        </td>
                        <td class="table-cell">
                            <div class="font-medium">{{ formatDate(renewal.renewal_date) }}</div>
                        </td>
                        <td class="table-cell">
                            <div class="flex items-center space-x-2">
                                <span :class="getDaysLeftClass(renewal.renewal_date)">
                                    {{ daysLeft(renewal.renewal_date) }} days
                                </span>
                                <span v-if="daysLeft(renewal.renewal_date) <= 3" class="text-red-500">⚠️</span>
                            </div>
                        </td>
                        <td class="table-cell font-medium text-gray-900 dark:text-white">
                            ${{ renewal.plan?.price }}
                        </td>
                        <td class="table-cell">
                            <SubscriptionStatusBadge :status="renewal.status" />
                        </td>
                        <td class="table-cell">
                            <button
                                @click="processRenewal(renewal)"
                                class="px-3 py-1 bg-teal-600 hover:bg-teal-700 text-white text-sm rounded-lg transition-colors flex items-center space-x-1"
                                :disabled="processingId === renewal.id"
                            >
                                <ArrowPathIcon class="w-4 h-4" :class="{ 'animate-spin': processingId === renewal.id }" />
                                <span>{{ processingId === renewal.id ? 'Processing...' : 'Process' }}</span>
                            </button>
                        </td>
                    </tr>
                    <tr v-if="renewals.length === 0">
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                            No renewals due
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import SubscriptionStatusBadge from './SubscriptionStatusBadge.vue'
import { ArrowPathIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    renewals: {
        type: Array,
        required: true
    }
})

const processingId = ref(null)

const sortedRenewals = computed(() => {
    return [...props.renewals].sort((a, b) => 
        new Date(a.renewal_date) - new Date(b.renewal_date)
    )
})

const totalAmount = computed(() => {
    return props.renewals.reduce((sum, r) => sum + (r.plan?.price || 0), 0).toFixed(2)
})

const dueThisWeek = computed(() => {
    const nextWeek = new Date()
    nextWeek.setDate(nextWeek.getDate() + 7)
    
    return props.renewals.filter(r => {
        const date = new Date(r.renewal_date)
        return date <= nextWeek
    }).length
})

const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const daysLeft = (date) => {
    if (!date) return 0
    const renewalDate = new Date(date)
    const today = new Date()
    const diffTime = renewalDate - today
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
    return diffDays > 0 ? diffDays : 0
}

const isUrgent = (date) => {
    return daysLeft(date) <= 3
}

const getDaysLeftClass = (date) => {
    const days = daysLeft(date)
    if (days <= 3) return 'text-red-600 font-medium'
    if (days <= 7) return 'text-yellow-600 font-medium'
    return 'text-green-600'
}

const processRenewal = (renewal) => {
    if (confirm(`Process renewal for ${renewal.client?.organization_name}?`)) {
        processingId.value = renewal.id
        router.post(`/subscriptions/renewals/${renewal.id}/process`, {}, {
            onSuccess: () => {
                processingId.value = null
                // Refresh the list
                window.location.reload()
            },
            onError: () => {
                processingId.value = null
                alert('Failed to process renewal')
            }
        })
    }
}
</script>