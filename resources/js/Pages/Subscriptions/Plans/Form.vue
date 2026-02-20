<template>
    <AuthenticatedLayout>
        <template #breadcrumbs>
            <div class="flex items-center space-x-2">
                <Link href="/dashboard">Dashboard</Link>
                <span>/</span>
                <Link href="/subscriptions/plans">Subscription Plans</Link>
                <span>/</span>
                <span class="text-gray-900 dark:text-white">{{ isEditing ? 'Edit' : 'Create' }} Plan</span>
            </div>
        </template>

        <div class="max-w-3xl mx-auto">
            <div class="card p-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                    {{ isEditing ? 'Edit Subscription Plan' : 'Create New Subscription Plan' }}
                </h2>

                <form @submit.prevent="submit">
                    <div class="space-y-6">
                        <!-- Plan Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Plan Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.plan_name"
                                type="text"
                                class="input-field"
                                :class="{ 'border-red-500': form.errors.plan_name }"
                                placeholder="e.g., HMS Basic"
                            />
                            <p v-if="form.errors.plan_name" class="mt-1 text-sm text-red-600">{{ form.errors.plan_name }}</p>
                        </div>

                        <!-- Price and Billing Cycle -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Price ($) <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="form.price"
                                    type="number"
                                    step="0.01"
                                    class="input-field"
                                    :class="{ 'border-red-500': form.errors.price }"
                                />
                                <p v-if="form.errors.price" class="mt-1 text-sm text-red-600">{{ form.errors.price }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Billing Cycle <span class="text-red-500">*</span>
                                </label>
                                <select
                                    v-model="form.billing_cycle"
                                    class="input-field"
                                    :class="{ 'border-red-500': form.errors.billing_cycle }"
                                >
                                    <option value="">Select cycle</option>
                                    <option v-for="cycle in billingCycles" :key="cycle.value" :value="cycle.value">
                                        {{ cycle.label }}
                                    </option>
                                </select>
                                <p v-if="form.errors.billing_cycle" class="mt-1 text-sm text-red-600">{{ form.errors.billing_cycle }}</p>
                            </div>
                        </div>

                        <!-- Trial Days and Support Level -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Trial Days
                                </label>
                                <input
                                    v-model="form.trial_days"
                                    type="number"
                                    class="input-field"
                                    placeholder="0 = no trial"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Support Level
                                </label>
                                <select v-model="form.support_level" class="input-field">
                                    <option value="standard">Standard</option>
                                    <option value="premium">Premium</option>
                                </select>
                            </div>
                        </div>

                        <!-- Max Users and Branches -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Max Users (Optional)
                                </label>
                                <input
                                    v-model="form.max_users"
                                    type="number"
                                    class="input-field"
                                    placeholder="Unlimited"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Max Branches (Optional)
                                </label>
                                <input
                                    v-model="form.max_branches"
                                    type="number"
                                    class="input-field"
                                    placeholder="Unlimited"
                                />
                            </div>
                        </div>

                        <!-- Modules Included -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Modules Included
                            </label>
                            <textarea
                                v-model="form.modules_included"
                                rows="3"
                                class="input-field"
                                placeholder='["HMS Core", "Patient Management", "Billing"]'
                            ></textarea>
                            <p class="mt-1 text-xs text-gray-500">Enter as JSON array</p>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Internal Notes
                            </label>
                            <textarea
                                v-model="form.notes"
                                rows="3"
                                class="input-field"
                                placeholder="Any additional information..."
                            ></textarea>
                        </div>

                        <!-- Active Status -->
                        <div class="flex items-center space-x-3">
                            <input
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                            />
                            <label class="text-sm text-gray-700 dark:text-gray-300">
                                Active (available for subscription)
                            </label>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <Link
                                href="/subscriptions/plans"
                                class="btn-outline"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                class="btn-primary"
                                :disabled="form.processing"
                            >
                                {{ form.processing ? 'Saving...' : (isEditing ? 'Update Plan' : 'Create Plan') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
    plan: Object,
    billingCycles: Array,
    supportLevels: Array
})

const isEditing = computed(() => !!props.plan)

const form = useForm({
    plan_name: props.plan?.plan_name || '',
    billing_cycle: props.plan?.billing_cycle || '',
    price: props.plan?.price || '',
    modules_included: props.plan?.modules_included ? JSON.stringify(props.plan.modules_included) : '',
    max_users: props.plan?.max_users || '',
    max_branches: props.plan?.max_branches || '',
    support_level: props.plan?.support_level || 'standard',
    notes: props.plan?.notes || '',
    trial_days: props.plan?.trial_days || 0,
    is_active: props.plan?.is_active ?? true
})

const submit = () => {
    if (isEditing.value) {
        form.put(`/subscriptions/plans/${props.plan.id}`)
    } else {
        form.post('/subscriptions/plans')
    }
}
</script>