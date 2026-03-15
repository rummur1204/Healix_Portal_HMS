<template>
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
        <!-- Search and Filter -->
        <div class="p-4 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="relative">
                    <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search client or subscription..."
                        class="input-field pl-10"
                    />
                </div>
                <select v-model="statusFilter" class="input-field">
                    <option value="">All Status</option>
                    <option value="trial">Trial</option>
                    <option value="active">Active</option>
                    <option value="past_due">Past Due</option>
                    <option value="suspended">Suspended</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <select v-model="planFilter" class="input-field">
                    <option value="">All Plans</option>
                    <option v-for="plan in uniquePlans" :key="plan.id" :value="plan.id">
                        {{ plan.plan_name }}
                    </option>
                </select>
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
                        <th class="table-header">Start Date</th>
                        <th class="table-header">End Date</th>
                        <th class="table-header">Status</th>
                        <th class="table-header">Payment</th>
                        <th class="table-header">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    <tr v-for="sub in filteredSubscriptions" :key="sub.id" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <td class="table-cell font-medium text-gray-900 dark:text-white">
                            <Link :href="`/subscriptions/subscription/${sub.id}`" class="hover:text-teal-600">
                                {{ sub.subscription_id }}
                            </Link>
                        </td>
                        <td class="table-cell">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-gradient-to-br from-teal-500 to-green-600 rounded-full flex items-center justify-center text-white text-sm font-medium mr-3">
                                    {{ sub.client?.organization_name?.charAt(0) || '?' }}
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">
                                        {{ sub.client?.organization_name }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ sub.client?.email }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="table-cell">
                            <div class="font-medium">{{ sub.plan?.plan_name }}</div>
                            <div class="text-xs text-gray-500">${{ sub.plan?.price }}/{{ sub.plan?.billing_cycle }}</div>
                        </td>
                        <td class="table-cell">{{ formatDate(sub.start_date) }}</td>
                        <td class="table-cell">
                            <div :class="isExpiringSoon(sub.end_date) ? 'text-red-600 font-medium' : ''">
                                {{ formatDate(sub.end_date) }}
                            </div>
                        </td>
                        <td class="table-cell">
                            <SubscriptionStatusBadge :status="sub.status" />
                        </td>
                        <td class="table-cell">
                            <div class="flex items-center space-x-2">
                                <span :class="getPaymentStatusClass(sub.payment_status)">
                                    {{ getPaymentStatusLabel(sub.payment_status) }}
                                </span>
                                <span v-if="sub.discount > 0" class="text-xs text-green-600">
                                    -${{ sub.discount }}
                                </span>
                            </div>
                        </td>
                        <td class="table-cell">
                            <div class="flex items-center space-x-2">
                                <Link
                                    :href="`/subscriptions/subscription/${sub.id}`"
                                    class="p-1 text-teal-600 hover:text-teal-800 dark:text-teal-400"
                                    title="View Details"
                                >
                                    <EyeIcon class="w-5 h-5" />
                                </Link>
                                <button
                                    @click="$emit('record-payment', sub)"
                                    class="p-1 text-green-600 hover:text-green-800 dark:text-green-400"
                                    title="Record Payment"
                                >
                                    <CurrencyDollarIcon class="w-5 h-5" />
                                </button>
                                <button
                                    v-if="sub.status === 'active'"
                                    @click="$emit('process-renewal', sub)"
                                    class="p-1 text-teal-600 hover:text-teal-800 dark:text-teal-400"
                                    title="Process Renewal"
                                >
                                    <ArrowPathIcon class="w-5 h-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="filteredSubscriptions.length === 0">
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                            No subscriptions found
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import SubscriptionStatusBadge from './SubscriptionStatusBadge.vue'
import {
    EyeIcon,
    CurrencyDollarIcon,
    ArrowPathIcon,
    MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
    subscriptions: {
        type: Array,
        required: true
    }
})

defineEmits(['record-payment', 'process-renewal'])

const search = ref('')
const statusFilter = ref('')
const planFilter = ref('')

const uniquePlans = computed(() => {
    const plans = {}
    props.subscriptions.forEach(sub => {
        if (sub.plan && !plans[sub.plan.id]) {
            plans[sub.plan.id] = sub.plan
        }
    })
    return Object.values(plans)
})

const filteredSubscriptions = computed(() => {
    return props.subscriptions.filter(sub => {
        const matchesSearch = !search.value || 
            sub.subscription_id.toLowerCase().includes(search.value.toLowerCase()) ||
            sub.client?.organization_name?.toLowerCase().includes(search.value.toLowerCase()) ||
            sub.client?.email?.toLowerCase().includes(search.value.toLowerCase())
        
        const matchesStatus = !statusFilter.value || sub.status === statusFilter.value
        const matchesPlan = !planFilter.value || sub.plan_id === parseInt(planFilter.value)
        
        return matchesSearch && matchesStatus && matchesPlan
    })
})

const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const isExpiringSoon = (date) => {
    if (!date) return false
    const end = new Date(date)
    const today = new Date()
    const diffDays = Math.ceil((end - today) / (1000 * 60 * 60 * 24))
    return diffDays <= 7 && diffDays > 0
}

const getPaymentStatusClass = (status) => {
    const map = {
        paid: 'px-2 py-1 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100 rounded-full text-xs',
        unpaid: 'px-2 py-1 bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100 rounded-full text-xs',
        partial: 'px-2 py-1 bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100 rounded-full text-xs',
    }
    return map[status] || 'px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs'
}

const getPaymentStatusLabel = (status) => {
    const map = {
        paid: 'Paid',
        unpaid: 'Unpaid',
        partial: 'Partial',
    }
    return map[status] || status
}
</script>