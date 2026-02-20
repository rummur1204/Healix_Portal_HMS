<template>
    <AuthenticatedLayout>
        <template #breadcrumbs>
            <div class="flex items-center space-x-2 text-sm">
                <Link href="/dashboard" class="text-gray-500 hover:text-teal-600 dark:text-gray-400 dark:hover:text-teal-400 transition-colors">Dashboard</Link>
                <ChevronRightIcon class="w-4 h-4 text-gray-400" />
                <span class="text-gray-900 dark:text-white font-medium">Subscriptions</span>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Header with gradient background -->
            <div class="relative overflow-hidden rounded-xl bg-gradient-to-r from-green-700 to-teal-600 p-8">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-32 translate-x-32"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-black/10 rounded-full -translate-x-24 translate-y-24"></div>
                
                <div class="relative">
                    <h2 class="text-3xl font-bold text-white">Subscription Management</h2>
                    <p class="text-teal-100 mt-2">Manage plans, client subscriptions, and renewals</p>
                </div>
            </div>

            <!-- Stats Cards Row -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <StatsCard
                    title="Total Plans"
                    :value="stats.total_plans"
                    :icon="CubeIcon"
                    iconBgClass="bg-teal-100 dark:bg-teal-900/30"
                    iconColorClass="text-teal-600 dark:text-teal-400"
                />
                
                <StatsCard
                    title="Active Subscriptions"
                    :value="stats.active_subscriptions"
                    :icon="CheckBadgeIcon"
                    iconBgClass="bg-green-100 dark:bg-green-900/30"
                    iconColorClass="text-green-600 dark:text-green-400"
                />
                
                <StatsCard
                    title="Monthly Revenue"
                    :value="`$${stats.monthly_revenue}`"
                    :icon="CurrencyDollarIcon"
                    iconBgClass="bg-green-100 dark:bg-green-900/30"
                    iconColorClass="text-green-600 dark:text-green-400"
                />
                
                <StatsCard
                    title="Renewals Due"
                    :value="stats.renewals_due"
                    :icon="ClockIcon"
                    iconBgClass="bg-yellow-100 dark:bg-yellow-900/30"
                    iconColorClass="text-yellow-600 dark:text-yellow-400"
                />
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200 dark:border-gray-800">
                <nav class="flex space-x-8">
                    <button
                        @click="activeTab = 'plans'"
                        class="py-4 px-1 relative group"
                        :class="activeTab === 'plans' ? 'text-teal-600 dark:text-teal-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                    >
                        <div class="flex items-center space-x-2">
                            <CubeIcon class="w-5 h-5" />
                            <span class="font-medium">Plans</span>
                        </div>
                        <!-- Active indicator -->
                        <div
                            v-if="activeTab === 'plans'"
                            class="absolute bottom-0 left-0 right-0 h-0.5 bg-teal-600 dark:bg-teal-400"
                        ></div>
                    </button>

                    <button
                        @click="activeTab = 'client-subscriptions'"
                        class="py-4 px-1 relative group"
                        :class="activeTab === 'client-subscriptions' ? 'text-teal-600 dark:text-teal-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                    >
                        <div class="flex items-center space-x-2">
                            <UsersIcon class="w-5 h-5" />
                            <span class="font-medium">Client Subscriptions</span>
                        </div>
                        <div
                            v-if="activeTab === 'client-subscriptions'"
                            class="absolute bottom-0 left-0 right-0 h-0.5 bg-teal-600 dark:bg-teal-400"
                        ></div>
                    </button>

                    <button
                        @click="activeTab = 'renewals'"
                        class="py-4 px-1 relative group"
                        :class="activeTab === 'renewals' ? 'text-teal-600 dark:text-teal-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                    >
                        <div class="flex items-center space-x-2">
                            <ArrowPathIcon class="w-5 h-5" />
                            <span class="font-medium">Renewals Due</span>
                            <span v-if="stats.renewals_due > 0" class="ml-2 px-2 py-0.5 text-xs bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100 rounded-full">
                                {{ stats.renewals_due }}
                            </span>
                        </div>
                        <div
                            v-if="activeTab === 'renewals'"
                            class="absolute bottom-0 left-0 right-0 h-0.5 bg-teal-600 dark:bg-teal-400"
                        ></div>
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="mt-6">
                <!-- Plans Tab -->
                <div v-if="activeTab === 'plans'" class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Subscription Plans</h3>
                        <Link
                            v-if="can.create_plans"
                            href="/subscriptions/plans/create"
                            class="btn-primary flex items-center space-x-2"
                        >
                            <PlusIcon class="w-5 h-5" />
                            <span>Create New Plan</span>
                        </Link>
                    </div>

                    <!-- Plans Table -->
                    <PlansTable :plans="plans" :can="can" @refresh="fetchPlans" />
                </div>

                <!-- Client Subscriptions Tab -->
                <div v-if="activeTab === 'client-subscriptions'" class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Client Subscriptions</h3>
                        <button
                            @click="showAssignModal = true"
                            v-if="can.assign_subscriptions"
                            class="btn-primary flex items-center space-x-2"
                        >
                            <PlusIcon class="w-5 h-5" />
                            <span>Assign Subscription</span>
                        </button>
                    </div>

                    <!-- Client Subscriptions Table -->
                    <ClientSubscriptionsTable 
                        :subscriptions="clientSubscriptions" 
                        @refresh="fetchClientSubscriptions"
                    />
                </div>

                <!-- Renewals Tab -->
                <div v-if="activeTab === 'renewals'" class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Renewals Due</h3>
                        <div class="flex items-center space-x-3">
                            <select 
                                v-model="renewalDays"
                                @change="fetchRenewals"
                                class="input-field text-sm py-2"
                            >
                                <option value="7">Next 7 Days</option>
                                <option value="15">Next 15 Days</option>
                                <option value="30">Next 30 Days</option>
                                <option value="60">Next 60 Days</option>
                            </select>
                            <button 
                                @click="exportRenewals"
                                class="btn-outline flex items-center space-x-2"
                            >
                                <ArrowDownTrayIcon class="w-5 h-5" />
                                <span>Export</span>
                            </button>
                        </div>
                    </div>

                    <!-- Renewals Table -->
                    <RenewalsTable :renewals="renewals" @refresh="fetchRenewals" />
                </div>
            </div>
        </div>

        <!-- Assign Subscription Modal -->
        <Modal :show="showAssignModal" @close="showAssignModal = false" maxWidth="2xl">
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Assign Subscription to Client</h3>
                
                <form @submit.prevent="assignSubscription">
                    <div class="space-y-4">
                        <!-- Client Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Client <span class="text-red-500">*</span>
                            </label>
                            <select v-model="assignForm.client_id" class="input-field" required>
                                <option value="">Select Client</option>
                                <option v-for="client in clients" :key="client.id" :value="client.id">
                                    {{ client.organization_name }}
                                </option>
                            </select>
                        </div>

                        <!-- Plan Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Plan <span class="text-red-500">*</span>
                            </label>
                            <select v-model="assignForm.plan_id" class="input-field" required>
                                <option value="">Select Plan</option>
                                <option v-for="plan in activePlans" :key="plan.id" :value="plan.id">
                                    {{ plan.plan_name }} - ${{ plan.price }}/{{ plan.billing_cycle }}
                                </option>
                            </select>
                        </div>

                        <!-- Start Date & Trial Option -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Start Date
                                </label>
                                <input
                                    v-model="assignForm.start_date"
                                    type="date"
                                    class="input-field"
                                    :min="new Date().toISOString().split('T')[0]"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    <input
                                        v-model="assignForm.is_trial"
                                        type="checkbox"
                                        class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-gray-300 rounded mr-2"
                                    />
                                    Start as Trial
                                </label>
                                <p class="text-xs text-gray-500 mt-1">Trial period will be based on plan settings</p>
                            </div>
                        </div>

                        <!-- Payment Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Payment Method
                                </label>
                                <select v-model="assignForm.payment_method" class="input-field">
                                    <option value="">Select</option>
                                    <option value="cash">Cash</option>
                                    <option value="bank">Bank Transfer</option>
                                    <option value="online">Online</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Payment Status
                                </label>
                                <select v-model="assignForm.payment_status" class="input-field">
                                    <option value="unpaid">Unpaid</option>
                                    <option value="paid">Paid</option>
                                    <option value="partial">Partial</option>
                                </select>
                            </div>
                        </div>

                        <!-- Discount & Invoice -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Discount Amount ($)
                                </label>
                                <input
                                    v-model="assignForm.discount"
                                    type="number"
                                    step="0.01"
                                    class="input-field"
                                />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Invoice Reference
                                </label>
                                <input
                                    v-model="assignForm.invoice_reference"
                                    type="text"
                                    class="input-field"
                                />
                            </div>
                        </div>

                        <!-- Internal Note -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Internal Note
                            </label>
                            <textarea
                                v-model="assignForm.internal_note"
                                rows="3"
                                class="input-field"
                                placeholder="Any additional notes..."
                            ></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <button
                            type="button"
                            @click="showAssignModal = false"
                            class="btn-outline"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="btn-primary"
                            :disabled="assignForm.processing"
                        >
                            {{ assignForm.processing ? 'Assigning...' : 'Assign Subscription' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatsCard from '@/Components/StatsCard.vue'
import Modal from '@/Components/Modal.vue'
import PlansTable from '@/Components/Subscription/PlansTable.vue'
import ClientSubscriptionsTable from '@/Components/Subscription/ClientSubscriptionsTable.vue'
import RenewalsTable from '@/Components/Subscription/RenewalsTable.vue'
import {
    ChevronRightIcon,
    CubeIcon,
    CheckBadgeIcon,
    CurrencyDollarIcon,
    ClockIcon,
    UsersIcon,
    ArrowPathIcon,
    PlusIcon,
    ArrowDownTrayIcon,
} from '@heroicons/vue/24/outline'

const activeTab = ref('plans')
const showAssignModal = ref(false)
const renewalDays = ref(30)

// Data
const plans = ref([])
const clientSubscriptions = ref([])
const renewals = ref([])
const clients = ref([])
const activePlans = ref([])

const stats = ref({
    total_plans: 0,
    active_subscriptions: 0,
    monthly_revenue: 0,
    renewals_due: 0
})

const can = ref({
    create_plans: false,
    edit_plans: false,
    delete_plans: false,
    assign_subscriptions: false,
    process_renewals: false,
})

// Assign Form
const assignForm = useForm({
    client_id: '',
    plan_id: '',
    start_date: '',
    is_trial: false,
    payment_method: '',
    payment_status: 'unpaid',
    discount: 0,
    invoice_reference: '',
    internal_note: '',
})

// Fetch all data
const fetchStats = async () => {
    try {
        const response = await fetch('/subscriptions/reports/summary')
        const data = await response.json()
        stats.value = data.stats
    } catch (error) {
        console.error('Failed to fetch stats:', error)
    }
}

const fetchPlans = async () => {
    try {
        const response = await fetch('/subscriptions/plans?per_page=100')
        const data = await response.json()
        plans.value = data.data || data
    } catch (error) {
        console.error('Failed to fetch plans:', error)
    }
}

const fetchClientSubscriptions = async () => {
    try {
        const response = await fetch('/subscriptions/client-subscriptions/all')
        const data = await response.json()
        clientSubscriptions.value = data.data || data
    } catch (error) {
        console.error('Failed to fetch client subscriptions:', error)
    }
}

const fetchRenewals = async () => {
    try {
        const response = await fetch(`/subscriptions/renewals/due/${renewalDays.value}`)
        const data = await response.json()
        renewals.value = data.data || data
    } catch (error) {
        console.error('Failed to fetch renewals:', error)
    }
}

const fetchClients = async () => {
    try {
        const response = await fetch('/clients/list')
        const data = await response.json()
        clients.value = data
    } catch (error) {
        console.error('Failed to fetch clients:', error)
    }
}

const fetchActivePlans = async () => {
    try {
        const response = await fetch('/subscriptions/plans/active')
        const data = await response.json()
        activePlans.value = data
    } catch (error) {
        console.error('Failed to fetch active plans:', error)
    }
}

const assignSubscription = () => {
    assignForm.post('/subscriptions/assign', {
        onSuccess: () => {
            showAssignModal.value = false
            assignForm.reset()
            fetchClientSubscriptions()
            fetchStats()
        }
    })
}

const exportRenewals = () => {
    window.location.href = `/subscriptions/reports/export/renewals?days=${renewalDays.value}`
}

onMounted(() => {
    fetchStats()
    fetchPlans()
    fetchClientSubscriptions()
    fetchRenewals()
    fetchClients()
    fetchActivePlans()
    
    // Get user permissions
    can.value = {
        create_plans: window.__user?.can?.('create subscription plans') || false,
        edit_plans: window.__user?.can?.('edit subscription plans') || false,
        delete_plans: window.__user?.can?.('delete subscription plans') || false,
        assign_subscriptions: window.__user?.can?.('assign subscriptions') || false,
        process_renewals: window.__user?.can?.('process renewals') || false,
    }
})
</script>