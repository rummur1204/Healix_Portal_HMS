<template>
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
        <!-- Search and Filter -->
        <div class="p-4 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
            <div class="flex items-center space-x-4">
                <div class="flex-1 relative">
                    <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search plans..."
                        class="input-field pl-10"
                        @input="filterPlans"
                    />
                </div>
                <select v-model="statusFilter" @change="filterPlans" class="input-field w-40">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="table-header">Plan Name</th>
                        <th class="table-header">Billing Cycle</th>
                        <th class="table-header">Price</th>
                        <th class="table-header">Trial Days</th>
                        <th class="table-header">Active Subs</th>
                        <th class="table-header">Status</th>
                        <th class="table-header">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    <tr v-for="plan in filteredPlans" :key="plan.id" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <td class="table-cell">
                            <div class="font-medium text-gray-900 dark:text-white">{{ plan.plan_name }}</div>
                            <div v-if="plan.max_users || plan.max_branches" class="text-xs text-gray-500 mt-1">
                                {{ plan.max_users ? `${plan.max_users} users` : '' }}
                                {{ plan.max_users && plan.max_branches ? ' • ' : '' }}
                                {{ plan.max_branches ? `${plan.max_branches} branches` : '' }}
                            </div>
                        </td>
                        <td class="table-cell">
                            <span class="capitalize px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded-full text-xs">
                                {{ plan.billing_cycle }}
                            </span>
                        </td>
                        <td class="table-cell font-medium text-gray-900 dark:text-white">${{ plan.price }}</td>
                        <td class="table-cell">{{ plan.trial_days }} days</td>
                        <td class="table-cell">
                            <span class="px-2 py-1 bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-100 rounded-full text-xs">
                                {{ plan.active_subscriptions_count || 0 }}
                            </span>
                        </td>
                        <td class="table-cell">
                            <span :class="plan.is_active ? 'badge-success' : 'badge-danger'">
                                {{ plan.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="table-cell">
                            <div class="flex items-center space-x-2">
                                <Link
                                    :href="`/subscriptions/plans/${plan.id}`"
                                    class="p-1 text-teal-600 hover:text-teal-800 dark:text-teal-400"
                                    title="View"
                                >
                                    <EyeIcon class="w-5 h-5" />
                                </Link>
                                <Link
                                    v-if="can.edit_plans"
                                    :href="`/subscriptions/plans/${plan.id}/edit`"
                                    class="p-1 text-green-600 hover:text-green-800 dark:text-green-400"
                                    title="Edit"
                                >
                                    <PencilIcon class="w-5 h-5" />
                                </Link>
                                <button
                                    v-if="can.edit_plans"
                                    @click="$emit('toggle-status', plan)"
                                    class="p-1 text-yellow-600 hover:text-yellow-800 dark:text-yellow-400"
                                    :title="plan.is_active ? 'Deactivate' : 'Activate'"
                                >
                                    <component :is="plan.is_active ? 'PauseIcon' : 'PlayIcon'" class="w-5 h-5" />
                                </button>
                                <button
                                    v-if="can.delete_plans"
                                    @click="$emit('delete', plan)"
                                    class="p-1 text-red-600 hover:text-red-800 dark:text-red-400"
                                    title="Delete"
                                >
                                    <TrashIcon class="w-5 h-5" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="filteredPlans.length === 0">
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                            No plans found
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="plans.meta" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            <Pagination :links="plans.meta.links" />
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import Pagination from '@/Components/Pagination.vue'
import {
    EyeIcon,
    PencilIcon,
    TrashIcon,
    PauseIcon,
    PlayIcon,
    MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
    plans: {
        type: [Array, Object],
        required: true
    },
    can: Object
})

defineEmits(['toggle-status', 'delete'])

const search = ref('')
const statusFilter = ref('')

const filteredPlans = computed(() => {
    if (!Array.isArray(props.plans)) {
        return props.plans.data || []
    }
    
    return props.plans.filter(plan => {
        const matchesSearch = plan.plan_name.toLowerCase().includes(search.value.toLowerCase())
        const matchesStatus = !statusFilter.value || 
            (statusFilter.value === 'active' ? plan.is_active : !plan.is_active)
        return matchesSearch && matchesStatus
    })
})
</script>