<template>
    <AuthenticatedLayout>
        <template #breadcrumbs>
            <div class="flex items-center space-x-2">
                <Link href="/dashboard" class="hover:text-primary-600">Dashboard</Link>
                <span>/</span>
                <span class="text-gray-900 dark:text-white">Subscription Plans</span>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Subscription Plans</h2>
                <Link
                    v-if="can.create"
                    href="/subscriptions/plans/create"
                    class="btn-primary flex items-center space-x-2"
                >
                    <PlusIcon class="w-5 h-5" />
                    <span>Create New Plan</span>
                </Link>
            </div>

            <!-- Filters -->
            <div class="card p-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Search plans..."
                            class="input-field"
                            @keyup.enter="applyFilters"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <select v-model="filters.status" class="input-field" @change="applyFilters">
                            <option value="">All</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button @click="applyFilters" class="btn-secondary px-6">
                            Apply Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Plans Table -->
            <div class="card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="table-header">Plan Name</th>
                                <th class="table-header">Billing Cycle</th>
                                <th class="table-header">Price</th>
                                <th class="table-header">Trial Days</th>
                                <th class="table-header">Active Subscriptions</th>
                                <th class="table-header">Status</th>
                                <th class="table-header">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="plan in plans.data" :key="plan.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="table-cell font-medium">{{ plan.plan_name }}</td>
                                <td class="table-cell">
                                    <span class="capitalize">{{ plan.billing_cycle }}</span>
                                </td>
                                <td class="table-cell">${{ plan.price }}</td>
                                <td class="table-cell">{{ plan.trial_days }} days</td>
                                <td class="table-cell">{{ plan.active_subscriptions_count || 0 }}</td>
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
                                            v-if="can.edit"
                                            :href="`/subscriptions/plans/${plan.id}/edit`"
                                            class="p-1 text-primary-600 hover:text-primary-800 dark:text-primary-400"
                                            title="Edit"
                                        >
                                            <PencilIcon class="w-5 h-5" />
                                        </Link>
                                        <button
                                            v-if="can.edit"
                                            @click="toggleStatus(plan)"
                                            class="p-1 text-yellow-600 hover:text-yellow-800 dark:text-yellow-400"
                                            :title="plan.is_active ? 'Deactivate' : 'Activate'"
                                        >
                                            <component :is="plan.is_active ? 'PauseIcon' : 'PlayIcon'" class="w-5 h-5" />
                                        </button>
                                        <button
                                            v-if="can.delete"
                                            @click="confirmDelete(plan)"
                                            class="p-1 text-red-600 hover:text-red-800 dark:text-red-400"
                                            title="Delete"
                                        >
                                            <TrashIcon class="w-5 h-5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    <Pagination :links="plans.links" />
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Delete Subscription Plan</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    Are you sure you want to delete "{{ selectedPlan?.plan_name }}"? This action cannot be undone.
                </p>
                <div class="flex justify-end space-x-3">
                    <button @click="showDeleteModal = false" class="btn-outline">
                        Cancel
                    </button>
                    <button @click="deletePlan" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                        Delete Plan
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'
import Pagination from '@/Components/Pagination.vue'
import {
    PlusIcon,
    EyeIcon,
    PencilIcon,
    TrashIcon,
    PauseIcon,
    PlayIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    plans: Object,
    filters: Object,
    can: Object
})

const showDeleteModal = ref(false)
const selectedPlan = ref(null)

const filters = ref({
    search: props.filters.search || '',
    status: props.filters.status || '',
})

const applyFilters = () => {
    router.get('/subscriptions/plans', filters.value, {
        preserveState: true,
        preserveScroll: true
    })
}

const toggleStatus = (plan) => {
    router.post(`/subscriptions/plans/${plan.id}/toggle-status`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Show success message
        }
    })
}

const confirmDelete = (plan) => {
    selectedPlan.value = plan
    showDeleteModal.value = true
}

const deletePlan = () => {
    router.delete(`/subscriptions/plans/${selectedPlan.value.id}`, {
        onSuccess: () => {
            showDeleteModal.value = false
            selectedPlan.value = null
        }
    })
}
</script>