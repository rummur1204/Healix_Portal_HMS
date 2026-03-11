<template>
    <AuthenticatedLayout>
        <template #breadcrumbs>
            <div class="flex items-center space-x-2">
                <Link href="/dashboard">Dashboard</Link>
                <span>/</span>
                <Link href="/subscriptions/plans">Subscription Plans</Link>
                <span>/</span>
                <span class="text-gray-900 dark:text-white">{{ plan?.plan_name }}</span>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Plan Details</h2>
                <div class="flex items-center space-x-3">
                    <Link
                        v-if="can?.edit"
                        :href="`/subscriptions/plans/${plan?.id}/edit`"
                        class="btn-secondary"
                    >
                        Edit Plan
                    </Link>
                </div>
            </div>

            <!-- Plan Details Card -->
            <div class="card p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Basic Information</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Plan Name:</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ plan?.plan_name }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Price:</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-white">${{ plan?.price }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Billing Cycle:</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-white capitalize">{{ plan?.billing_cycle }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Trial Days:</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ plan?.trial_days }} days</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Support Level:</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-white capitalize">{{ plan?.support_level }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Status:</dt>
                                <dd>
                                    <span :class="plan?.is_active ? 'badge-success' : 'badge-danger'">
                                        {{ plan?.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Limits & Features</h3>
                        <dl class="space-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Max Users:</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ plan?.max_users || 'Unlimited' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500 dark:text-gray-400">Max Branches:</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ plan?.max_branches || 'Unlimited' }}</dd>
                            </div>
                        </dl>

                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mt-6 mb-4">Modules Included</h3>
                        <div class="space-y-2">
                            <div v-if="modules.length">
                                <span 
                                    v-for="module in modules" 
                                    :key="module"
                                    class="inline-block px-3 py-1 mr-2 mb-2 bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-100 rounded-full text-sm"
                                >
                                    {{ module }}
                                </span>
                            </div>
                            <p v-else class="text-sm text-gray-500">No modules specified</p>
                        </div>

                        <div v-if="plan?.notes" class="mt-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Notes</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ plan?.notes }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Subscriptions -->
            <div class="card p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Subscriptions</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left py-3 text-sm font-medium text-gray-500 dark:text-gray-400">Client</th>
                                <th class="text-left py-3 text-sm font-medium text-gray-500 dark:text-gray-400">Subscription ID</th>
                                <th class="text-left py-3 text-sm font-medium text-gray-500 dark:text-gray-400">Start Date</th>
                                <th class="text-left py-3 text-sm font-medium text-gray-500 dark:text-gray-400">End Date</th>
                                <th class="text-left py-3 text-sm font-medium text-gray-500 dark:text-gray-400">Status</th>
                                <th class="text-left py-3 text-sm font-medium text-gray-500 dark:text-gray-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="sub in plan?.subscriptions || []" :key="sub.id" class="border-b border-gray-100 dark:border-gray-800">
                                <td class="py-3 text-sm text-gray-900 dark:text-white">
                                    <Link :href="`/clients/${sub.client?.id}`" class="text-primary-600 hover:underline">
                                        {{ sub.client?.organization_name }}
                                    </Link>
                                </td>
                                <td class="py-3 text-sm text-gray-600 dark:text-gray-400">{{ sub?.subscription_id }}</td>
                                <td class="py-3 text-sm text-gray-600 dark:text-gray-400">{{ formatDate(sub?.start_date) }}</td>
                                <td class="py-3 text-sm text-gray-600 dark:text-gray-400">{{ formatDate(sub?.end_date) }}</td>
                                <td class="py-3">
                                    <SubscriptionStatusBadge :status="sub?.status" />
                                </td>
                                <td class="py-3">
                                    <Link :href="`/subscriptions/subscription/${sub?.id}`" class="text-teal-600 hover:text-teal-800">
                                        View
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="!plan?.subscriptions?.length">
                                <td colspan="6" class="py-6 text-center text-gray-500 dark:text-gray-400">
                                    No subscriptions yet for this plan
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
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SubscriptionStatusBadge from '@/Components/Subscription/SubscriptionStatusBadge.vue'

const props = defineProps({
    plan: Object,
    can: Object
})

const modules = computed(() => {
    if (!props.plan?.modules_included) return []
    if (Array.isArray(props.plan?.modules_included)) return props.plan.modules_included
    try {
        return JSON.parse(props.plan?.modules_included || '[]')
    } catch {
        return []
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
</script>