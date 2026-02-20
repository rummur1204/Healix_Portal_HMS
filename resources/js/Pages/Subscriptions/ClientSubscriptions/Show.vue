<template>
    <AuthenticatedLayout>
        <template #breadcrumbs>
            <div class="flex items-center space-x-2 text-sm">
                <Link href="/dashboard" class="text-gray-500 hover:text-teal-600 dark:text-gray-400 dark:hover:text-teal-400 transition-colors">Dashboard</Link>
                <ChevronRightIcon class="w-4 h-4 text-gray-400" />
                <Link :href="`/clients/${subscription?.client?.id}`" class="text-gray-500 hover:text-teal-600 dark:text-gray-400 dark:hover:text-teal-400 transition-colors">
                    {{ subscription?.client?.organization_name }}
                </Link>
                <ChevronRightIcon class="w-4 h-4 text-gray-400" />
                <span class="text-gray-900 dark:text-white font-medium">{{ subscription?.subscription_id }}</span>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Header with gradient background -->
            <div class="relative overflow-hidden rounded-xl bg-gradient-to-r from-green-700 to-teal-600 p-8">
                <!-- Decorative elements -->
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-32 translate-x-32"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-black/10 rounded-full -translate-x-24 translate-y-24"></div>
                
                <div class="relative flex items-center justify-between">
                    <div>
                        <div class="flex items-center space-x-3">
                            <h2 class="text-3xl font-bold text-white">{{ subscription?.subscription_id }}</h2>
                            <SubscriptionStatusBadge :status="subscription?.status" />
                        </div>
                        <p class="text-teal-100 mt-2 flex items-center">
                            <UserIcon class="w-4 h-4 mr-1" />
                            {{ subscription?.client?.organization_name }}
                            <span class="mx-2">•</span>
                            <CalendarIcon class="w-4 h-4 mr-1" />
                            Started {{ formatDate(subscription?.start_date) }}
                        </p>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex items-center space-x-3">
                        <button 
                            v-if="can?.cancel && ['active', 'trial'].includes(subscription?.status)"
                            @click="confirmCancel"
                            class="px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-lg backdrop-blur-sm transition-all flex items-center space-x-2"
                        >
                            <XCircleIcon class="w-5 h-5" />
                            <span>Cancel</span>
                        </button>
                        <Link
                            :href="`/subscriptions/subscription/${subscription?.id}/edit`"
                            v-if="can?.edit"
                            class="px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-lg backdrop-blur-sm transition-all flex items-center space-x-2"
                        >
                            <PencilIcon class="w-5 h-5" />
                            <span>Edit</span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Stats Cards Row -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <StatsCard
                    title="Plan"
                    :value="subscription?.plan?.plan_name"
                    :icon="CubeIcon"
                    iconBgClass="bg-green-100 dark:bg-green-900/30"
                    iconColorClass="text-green-600 dark:text-green-400"
                    :footer="`$${subscription?.plan?.price} / ${subscription?.plan?.billing_cycle}`"
                />
                
                <StatsCard
                    title="Payment Status"
                    :value="getStatusLabel(subscription?.payment_status)"
                    :icon="CurrencyDollarIcon"
                    :iconBgClass="paymentStatusBgClass"
                    :iconColorClass="paymentStatusIconClass"
                />
                
                <StatsCard
                    title="Days Remaining"
                    :value="daysRemaining"
                    :icon="ClockIcon"
                    iconBgClass="bg-teal-100 dark:bg-teal-900/30"
                    iconColorClass="text-teal-600 dark:text-teal-400"
                    :progress="daysProgress"
                />
                
                <StatsCard
                    title="Total Paid"
                    :value="`$${totalPaid}`"
                    :icon="BanknotesIcon"
                    iconBgClass="bg-green-100 dark:bg-green-900/30"
                    iconColorClass="text-green-600 dark:text-green-400"
                />
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column - Subscription Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Plan Details Card -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-800">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                <DocumentTextIcon class="w-5 h-5 mr-2 text-teal-600" />
                                Subscription Details
                            </h3>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Plan</dt>
                                    <dd class="mt-1 text-base text-gray-900 dark:text-white">{{ subscription?.plan?.plan_name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Billing Cycle</dt>
                                    <dd class="mt-1 text-base text-gray-900 dark:text-white capitalize">{{ subscription?.plan?.billing_cycle }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Start Date</dt>
                                    <dd class="mt-1 text-base text-gray-900 dark:text-white">{{ formatDate(subscription?.start_date) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">End Date</dt>
                                    <dd class="mt-1 text-base text-gray-900 dark:text-white">{{ formatDate(subscription?.end_date) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Renewal Date</dt>
                                    <dd class="mt-1 text-base text-gray-900 dark:text-white">{{ formatDate(subscription?.renewal_date) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Trial Ends</dt>
                                    <dd class="mt-1 text-base text-gray-900 dark:text-white">{{ subscription?.trial_end_date ? formatDate(subscription.trial_end_date) : 'No trial' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Payment Method</dt>
                                    <dd class="mt-1 text-base text-gray-900 dark:text-white capitalize">{{ subscription?.payment_method || 'Not specified' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Discount</dt>
                                    <dd class="mt-1 text-base text-gray-900 dark:text-white">{{ subscription?.discount ? '$' + subscription.discount : 'No discount' }}</dd>
                                </div>
                            </dl>

                            <!-- Modules Included -->
                            <div v-if="modules.length" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-800">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Modules Included</h4>
                                <div class="flex flex-wrap gap-2">
                                    <span 
                                        v-for="module in modules" 
                                        :key="module"
                                        class="px-3 py-1 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100 rounded-full text-sm"
                                    >
                                        {{ module }}
                                    </span>
                                </div>
                            </div>

                            <!-- Internal Note -->
                            <div v-if="subscription?.internal_note" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-800">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Internal Note</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 p-3 rounded-lg">
                                    {{ subscription.internal_note }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment History -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-800">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                <BanknotesIcon class="w-5 h-5 mr-2 text-teal-600" />
                                Payment History
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Transaction ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Method</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                    <tr v-for="payment in subscription?.payments || []" :key="payment.id" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ payment.transaction_id }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ formatDate(payment.payment_date) }}</td>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">${{ payment.amount }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400 capitalize">{{ payment.payment_method }}</td>
                                        <td class="px-6 py-4">
                                            <span :class="getStatusColor(payment.status)" class="px-2 py-1 text-xs rounded-full">
                                                {{ getStatusLabel(payment.status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="!subscription?.payments?.length">
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                            No payments recorded yet
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Timeline & Actions -->
                <div class="space-y-6">
                    <!-- Quick Actions -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-800">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                <Cog6ToothIcon class="w-5 h-5 mr-2 text-teal-600" />
                                Quick Actions
                            </h3>
                        </div>
                        <div class="p-6 space-y-3">
                            <button 
                                v-if="can?.record_payment"
                                @click="showPaymentModal = true"
                                class="w-full flex items-center justify-between p-3 bg-green-50 hover:bg-green-100 dark:bg-green-900/20 dark:hover:bg-green-900/30 rounded-lg transition-colors group"
                            >
                                <span class="flex items-center text-green-700 dark:text-green-400">
                                    <PlusIcon class="w-5 h-5 mr-2" />
                                    Record Payment
                                </span>
                                <ArrowRightIcon class="w-4 h-4 text-green-500 opacity-0 group-hover:opacity-100 transition-opacity" />
                            </button>
                            
                            <button 
                                v-if="can?.process_renewal && subscription?.status === 'active'"
                                @click="processRenewal"
                                class="w-full flex items-center justify-between p-3 bg-teal-50 hover:bg-teal-100 dark:bg-teal-900/20 dark:hover:bg-teal-900/30 rounded-lg transition-colors group"
                            >
                                <span class="flex items-center text-teal-700 dark:text-teal-400">
                                    <ArrowPathIcon class="w-5 h-5 mr-2" />
                                    Process Renewal
                                </span>
                                <ArrowRightIcon class="w-4 h-4 text-teal-500 opacity-0 group-hover:opacity-100 transition-opacity" />
                            </button>
                            
                            <button 
                                v-if="can?.suspend && subscription?.status === 'active'"
                                @click="confirmSuspend"
                                class="w-full flex items-center justify-between p-3 bg-yellow-50 hover:bg-yellow-100 dark:bg-yellow-900/20 dark:hover:bg-yellow-900/30 rounded-lg transition-colors group"
                            >
                                <span class="flex items-center text-yellow-700 dark:text-yellow-400">
                                    <PauseIcon class="w-5 h-5 mr-2" />
                                    Suspend Subscription
                                </span>
                                <ArrowRightIcon class="w-4 h-4 text-yellow-500 opacity-0 group-hover:opacity-100 transition-opacity" />
                            </button>
                            
                            <button 
                                v-if="can?.reactivate && subscription?.status === 'suspended'"
                                @click="reactivateSubscription"
                                class="w-full flex items-center justify-between p-3 bg-green-50 hover:bg-green-100 dark:bg-green-900/20 dark:hover:bg-green-900/30 rounded-lg transition-colors group"
                            >
                                <span class="flex items-center text-green-700 dark:text-green-400">
                                    <PlayIcon class="w-5 h-5 mr-2" />
                                    Reactivate Subscription
                                </span>
                                <ArrowRightIcon class="w-4 h-4 text-green-500 opacity-0 group-hover:opacity-100 transition-opacity" />
                            </button>
                        </div>
                    </div>

                    <!-- History Timeline -->
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-800">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                <ClockIcon class="w-5 h-5 mr-2 text-teal-600" />
                                Activity Timeline
                            </h3>
                        </div>
                        <div class="p-6 max-h-96 overflow-y-auto">
                            <div class="relative">
                                <!-- Timeline line -->
                                <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>
                                
                                <div v-for="(event, index) in subscription?.history" :key="event.id" class="relative pl-10 pb-6">
                                    <!-- Timeline dot -->
                                    <div class="absolute left-2 w-4 h-4 rounded-full" :class="getTimelineDotColor(event.action)"></div>
                                    
                                    <!-- Content -->
                                    <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-lg">
                                        <p class="text-sm text-gray-900 dark:text-white">{{ event.notes || getActionLabel(event.action) }}</p>
                                        <div class="flex items-center mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            <UserIcon class="w-3 h-3 mr-1" />
                                            {{ event.performed_by?.name || 'System' }}
                                            <span class="mx-1">•</span>
                                            <ClockIcon class="w-3 h-3 mr-1" />
                                            {{ formatDateTime(event.created_at) }}
                                        </div>
                                    </div>
                                </div>
                                
                                <div v-if="!subscription?.history?.length" class="relative pl-10">
                                    <div class="absolute left-2 w-4 h-4 rounded-full bg-gray-300 dark:bg-gray-600"></div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">No activity recorded yet</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Modal -->
        <Modal :show="showPaymentModal" @close="showPaymentModal = false">
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Record Payment</h3>
                
                <form @submit.prevent="recordPayment">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Amount <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">$</span>
                                <input
                                    v-model="paymentForm.amount"
                                    type="number"
                                    step="0.01"
                                    class="input-field pl-8"
                                    :class="{ 'border-red-500': paymentForm.errors.amount }"
                                    required
                                />
                            </div>
                            <p v-if="paymentForm.errors.amount" class="mt-1 text-xs text-red-600">{{ paymentForm.errors.amount }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Payment Method <span class="text-red-500">*</span>
                            </label>
                            <select v-model="paymentForm.payment_method" class="input-field" required>
                                <option value="">Select method</option>
                                <option value="cash">Cash</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="online">Online Payment</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Payment Date
                            </label>
                            <input
                                v-model="paymentForm.payment_date"
                                type="date"
                                class="input-field"
                                :max="new Date().toISOString().split('T')[0]"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Reference Number
                            </label>
                            <input
                                v-model="paymentForm.reference_number"
                                type="text"
                                class="input-field"
                                placeholder="e.g., Transaction ID"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Notes
                            </label>
                            <textarea
                                v-model="paymentForm.notes"
                                rows="3"
                                class="input-field"
                                placeholder="Any additional information..."
                            ></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <button
                            type="button"
                            @click="showPaymentModal = false"
                            class="btn-outline"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="btn-primary"
                            :disabled="paymentForm.processing"
                        >
                            {{ paymentForm.processing ? 'Recording...' : 'Record Payment' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Cancel Modal -->
        <Modal :show="showCancelModal" @close="showCancelModal = false">
            <div class="p-6">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 dark:bg-red-900/30 rounded-full mb-4">
                    <ExclamationTriangleIcon class="w-6 h-6 text-red-600 dark:text-red-400" />
                </div>
                
                <h3 class="text-lg font-medium text-gray-900 dark:text-white text-center mb-2">Cancel Subscription</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 text-center mb-6">
                    Are you sure you want to cancel this subscription? This action cannot be undone.
                </p>
                
                <textarea
                    v-model="cancelReason"
                    placeholder="Please provide a reason for cancellation..."
                    class="input-field mb-4"
                    rows="3"
                ></textarea>
                
                <div class="flex justify-end space-x-3">
                    <button @click="showCancelModal = false" class="btn-outline">
                        Close
                    </button>
                    <button @click="cancelSubscription" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                        Confirm Cancellation
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Suspend Modal -->
        <Modal :show="showSuspendModal" @close="showSuspendModal = false">
            <div class="p-6">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-yellow-100 dark:bg-yellow-900/30 rounded-full mb-4">
                    <PauseIcon class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                </div>
                
                <h3 class="text-lg font-medium text-gray-900 dark:text-white text-center mb-2">Suspend Subscription</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 text-center mb-6">
                    This will temporarily suspend access for the client.
                </p>
                
                <textarea
                    v-model="suspendReason"
                    placeholder="Reason for suspension..."
                    class="input-field mb-4"
                    rows="3"
                ></textarea>
                
                <div class="flex justify-end space-x-3">
                    <button @click="showSuspendModal = false" class="btn-outline">
                        Cancel
                    </button>
                    <button @click="suspendSubscription" class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg">
                        Confirm Suspension
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SubscriptionStatusBadge from '@/Components/Subscription/SubscriptionStatusBadge.vue'
import StatsCard from '@/Components/StatsCard.vue'
import Modal from '@/Components/Modal.vue'
import { useColors } from '@/Composables/useColors'
import {
    ChevronRightIcon,
    UserIcon,
    CalendarIcon,
    XCircleIcon,
    PencilIcon,
    CubeIcon,
    CurrencyDollarIcon,
    ClockIcon,
    BanknotesIcon,
    DocumentTextIcon,
    Cog6ToothIcon,
    PlusIcon,
    ArrowRightIcon,
    ArrowPathIcon,
    PauseIcon,
    PlayIcon,
    ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
    subscription: Object,
    can: Object
})

const { getStatusColor, getStatusLabel } = useColors()

// Computed properties
const modules = computed(() => {
    if (!props.subscription?.plan?.modules_included) return []
    if (Array.isArray(props.subscription.plan.modules_included)) return props.subscription.plan.modules_included
    try {
        return JSON.parse(props.subscription.plan.modules_included || '[]')
    } catch {
        return []
    }
})

const daysRemaining = computed(() => {
    if (!props.subscription?.end_date) return 0
    const end = new Date(props.subscription.end_date)
    const today = new Date()
    const diffTime = end - today
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
    return diffDays > 0 ? diffDays : 0
})

const daysProgress = computed(() => {
    if (!props.subscription?.start_date || !props.subscription?.end_date) return 0
    const start = new Date(props.subscription.start_date)
    const end = new Date(props.subscription.end_date)
    const today = new Date()
    const total = end - start
    const elapsed = today - start
    const progress = (elapsed / total) * 100
    return Math.min(Math.max(Math.round(progress), 0), 100)
})

const totalPaid = computed(() => {
    return props.subscription?.payments?.reduce((sum, p) => sum + Number(p.amount), 0) || 0
})

const paymentStatusBgClass = computed(() => {
    const map = {
        paid: 'bg-green-100 dark:bg-green-900/30',
        unpaid: 'bg-red-100 dark:bg-red-900/30',
        partial: 'bg-yellow-100 dark:bg-yellow-900/30',
    }
    return map[props.subscription?.payment_status] || 'bg-gray-100 dark:bg-gray-900/30'
})

const paymentStatusIconClass = computed(() => {
    const map = {
        paid: 'text-green-600 dark:text-green-400',
        unpaid: 'text-red-600 dark:text-red-400',
        partial: 'text-yellow-600 dark:text-yellow-400',
    }
    return map[props.subscription?.payment_status] || 'text-gray-600 dark:text-gray-400'
})

// Modal states
const showCancelModal = ref(false)
const showSuspendModal = ref(false)
const showPaymentModal = ref(false)
const cancelReason = ref('')
const suspendReason = ref('')

// Payment form
const paymentForm = useForm({
    amount: props.subscription?.plan?.price || '',
    payment_method: '',
    payment_date: new Date().toISOString().split('T')[0],
    reference_number: '',
    notes: '',
})

// Helper functions
const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const formatDateTime = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const getActionLabel = (action) => {
    const map = {
        created: 'Subscription created',
        renewed: 'Subscription renewed',
        upgraded: 'Plan upgraded',
        downgraded: 'Plan downgraded',
        cancelled: 'Subscription cancelled',
        suspended: 'Subscription suspended',
        reactivated: 'Subscription reactivated',
        status_changed: 'Status changed',
        payment_received: 'Payment received',
    }
    return map[action] || action
}

const getTimelineDotColor = (action) => {
    const map = {
        created: 'bg-green-500',
        renewed: 'bg-teal-500',
        upgraded: 'bg-green-500',
        downgraded: 'bg-yellow-500',
        cancelled: 'bg-red-500',
        suspended: 'bg-yellow-500',
        reactivated: 'bg-green-500',
        status_changed: 'bg-blue-500',
        payment_received: 'bg-teal-500',
    }
    return map[action] || 'bg-gray-500'
}

// Actions
const confirmCancel = () => {
    showCancelModal.value = true
}

const cancelSubscription = () => {
    router.put(`/subscriptions/subscription/${props.subscription?.id}/cancel`, {
        reason: cancelReason.value
    }, {
        onSuccess: () => {
            showCancelModal.value = false
            cancelReason.value = ''
        }
    })
}

const confirmSuspend = () => {
    showSuspendModal.value = true
}

const suspendSubscription = () => {
    router.put(`/subscriptions/subscription/${props.subscription?.id}/suspend`, {
        reason: suspendReason.value
    }, {
        onSuccess: () => {
            showSuspendModal.value = false
            suspendReason.value = ''
        }
    })
}

const reactivateSubscription = () => {
    router.put(`/subscriptions/subscription/${props.subscription?.id}/reactivate`, {}, {
        onSuccess: () => {
            // Show success message
        }
    })
}

const processRenewal = () => {
    if (confirm('Process renewal for this subscription?')) {
        router.post(`/subscriptions/renewals/${props.subscription?.id}/process`, {}, {
            onSuccess: () => {
                // Show success message
            }
        })
    }
}

const recordPayment = () => {
    paymentForm.post('/subscriptions/payments/record', {
        onSuccess: () => {
            showPaymentModal.value = false
            paymentForm.reset()
        }
    })
}
</script>