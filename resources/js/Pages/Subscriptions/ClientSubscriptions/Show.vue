<template>
    <AuthenticatedLayout>
        <template #breadcrumbs>
            <div class="flex items-center space-x-2 text-sm">
                <Link href="/dashboard" class="text-gray-500 hover:text-teal-600 dark:text-gray-400 dark:hover:text-teal-400 transition-colors">Dashboard</Link>
                <ChevronRightIcon class="w-4 h-4 text-gray-400" />
                <Link href="/subscriptions" class="text-gray-500 hover:text-teal-600 dark:text-gray-400 dark:hover:text-teal-400 transition-colors">Subscriptions</Link>
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
                            <span v-if="subscription?.trial_end_date && isTrialActive" 
                                  class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                Trial ends {{ formatDate(subscription.trial_end_date) }}
                            </span>
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
                        <Link
                            :href="`/subscriptions/subscription/${subscription?.id}/edit`"
                            v-if="can?.edit"
                            class="px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-lg backdrop-blur-sm transition-all flex items-center space-x-2"
                        >
                            <PencilIcon class="w-5 h-5" />
                            <span>Edit</span>
                        </Link>
                        <button
                            @click="confirmDelete"
                            v-if="can?.delete"
                            class="px-4 py-2 bg-red-500/20 hover:bg-red-500/30 text-white rounded-lg backdrop-blur-sm transition-all flex items-center space-x-2"
                        >
                            <TrashIcon class="w-5 h-5" />
                            <span>Delete</span>
                        </button>
                        <button 
                            v-if="can?.cancel && ['active', 'trial'].includes(subscription?.status)"
                            @click="confirmCancel"
                            class="px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-lg backdrop-blur-sm transition-all flex items-center space-x-2"
                        >
                            <XCircleIcon class="w-5 h-5" />
                            <span>Cancel</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Payment Status Banner -->
            <div class="rounded-xl p-4" :class="paymentBannerClass">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-white/20 rounded-lg">
                            <CurrencyDollarIcon class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <p class="text-white/80 text-sm">Payment Status</p>
                            <p class="text-white font-bold text-xl">{{ getPaymentStatusLabel(subscription?.payment_status) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <p class="text-white/80 text-sm">Total Paid</p>
                            <p class="text-white font-bold text-xl">${{ totalPaid }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-white/80 text-sm">Plan Price</p>
                            <p class="text-white font-bold text-xl">${{ subscription?.plan?.price }}</p>
                        </div>
                    </div>
                </div>
                <!-- Progress bar for partial payments -->
                <div v-if="subscription?.payment_status === 'partial'" class="mt-3">
                    <div class="w-full bg-white/20 rounded-full h-2">
                        <div 
                            class="bg-white rounded-full h-2 transition-all duration-500"
                            :style="{ width: paymentProgress + '%' }"
                        ></div>
                    </div>
                    <p class="text-white/80 text-xs mt-1">{{ paymentProgress }}% paid</p>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column - Subscription Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Subscription Details Card -->
                    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
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
                                    <dd class="mt-1 text-base text-gray-900 dark:text-white" :class="{ 'text-red-600 font-semibold': isExpiringSoon }">
                                        {{ formatDate(subscription?.end_date) }}
                                        <span v-if="isExpiringSoon" class="ml-2 text-xs bg-red-100 text-red-800 px-2 py-0.5 rounded-full">Expiring soon</span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Renewal Date</dt>
                                    <dd class="mt-1 text-base text-gray-900 dark:text-white">{{ formatDate(subscription?.renewal_date) }}</dd>
                                </div>
                                <div v-if="subscription?.trial_end_date">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Trial Period</dt>
                                    <dd class="mt-1 text-base text-gray-900 dark:text-white">
                                        {{ formatDate(subscription.trial_end_date) }}
                                        <span v-if="isTrialActive" class="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full">Active</span>
                                        <span v-else-if="isTrialExpired" class="ml-2 text-xs bg-gray-100 text-gray-800 px-2 py-0.5 rounded-full">Expired</span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Payment Method</dt>
                                    <dd class="mt-1 text-base text-gray-900 dark:text-white capitalize">{{ subscription?.payment_method || 'Not specified' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Discount</dt>
                                    <dd class="mt-1 text-base text-gray-900 dark:text-white">
                                        {{ subscription?.discount ? '$' + subscription.discount : 'No discount' }}
                                        <span v-if="needsApproval" class="ml-2 text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded-full">Pending Approval</span>
                                    </dd>
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
                                <p class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-900 p-3 rounded-lg">
                                    {{ subscription.internal_note }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment History -->
                    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                <BanknotesIcon class="w-5 h-5 mr-2 text-teal-600" />
                                Payment History
                            </h3>
                            <button 
                                v-if="can?.record_payment"
                                @click="showPaymentModal = true"
                                class="inline-flex items-center px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white text-sm rounded-lg transition-colors"
                            >
                                <PlusIcon class="w-4 h-4 mr-1" />
                                Record Payment
                            </button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Transaction ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Method</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                    <tr v-for="payment in subscription?.payments || []" :key="payment.id" class="hover:bg-gray-50 dark:hover:bg-gray-900 transition-colors">
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ payment.transaction_id }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ formatDate(payment.payment_date) }}</td>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">${{ payment.amount }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400 capitalize">{{ payment.payment_method }}</td>
                                        <td class="px-6 py-4">
                                            <span :class="getPaymentRecordStatusClass(payment.status)" class="px-2 py-1 text-xs rounded-full">
                                                {{ payment.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <button @click="voidPayment(payment)" class="text-red-600 hover:text-red-800 text-sm">Void</button>
                                        </td>
                                    </tr>
                                    <tr v-if="!subscription?.payments?.length">
                                        <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
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
                    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-800">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                <Cog6ToothIcon class="w-5 h-5 mr-2 text-teal-600" />
                                Quick Actions
                            </h3>
                        </div>
                        <div class="p-6 space-y-3">
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

                            <button 
                                v-if="can?.upgrade_downgrade"
                                @click="showChangePlanModal = true"
                                class="w-full flex items-center justify-between p-3 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:hover:bg-blue-900/30 rounded-lg transition-colors group"
                            >
                                <span class="flex items-center text-blue-700 dark:text-blue-400">
                                    <ArrowUpIcon class="w-5 h-5 mr-2" />
                                    Change Plan
                                </span>
                                <ArrowRightIcon class="w-4 h-4 text-blue-500 opacity-0 group-hover:opacity-100 transition-opacity" />
                            </button>
                        </div>
                    </div>

                    <!-- History Timeline (FR-SUB-04) -->
                    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-800">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                <ClockIcon class="w-5 h-5 mr-2 text-teal-600" />
                                Activity Timeline
                            </h3>
                        </div>
                        <div class="p-6 max-h-96 overflow-y-auto">
                            <div class="relative">
                                <!-- Timeline line -->
                                <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-800"></div>
                                
                                <div v-for="(event, index) in subscription?.history" :key="event.id" class="relative pl-10 pb-6">
                                    <!-- Timeline dot -->
                                    <div class="absolute left-2 w-4 h-4 rounded-full" :class="getTimelineDotColor(event.action)"></div>
                                    
                                    <!-- Content -->
                                    <div class="bg-gray-50 dark:bg-gray-900 p-3 rounded-lg">
                                        <p class="text-sm text-gray-900 dark:text-white">{{ event.notes || getActionLabel(event.action) }}</p>
                                        <div class="flex items-center mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            <UserIcon class="w-3 h-3 mr-1" />
                                            {{ event.performed_by?.name || 'System' }}
                                            <span class="mx-1">•</span>
                                            <ClockIcon class="w-3 h-3 mr-1" />
                                            {{ formatDateTime(event.created_at) }}
                                        </div>
                                        <!-- Show change details -->
                                        <div v-if="event.old_status || event.new_status" class="mt-2 text-xs">
                                            <span class="text-gray-500">Status:</span>
                                            <span class="mx-1 px-1.5 py-0.5 bg-gray-200 dark:bg-gray-800 rounded">{{ event.old_status || 'None' }}</span>
                                            <span class="text-gray-500">→</span>
                                            <span class="mx-1 px-1.5 py-0.5 bg-gray-200 dark:bg-gray-800 rounded">{{ event.new_status }}</span>
                                        </div>
                                        <div v-if="event.old_plan_id || event.new_plan_id" class="mt-1 text-xs">
                                            <span class="text-gray-500">Plan changed</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div v-if="!subscription?.history?.length" class="relative pl-10">
                                    <div class="absolute left-2 w-4 h-4 rounded-full bg-gray-300 dark:bg-gray-700"></div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">No activity recorded yet</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Modal (FIXED) -->
        <Modal :show="showPaymentModal" @close="showPaymentModal = false">
            <div class="p-6 bg-white dark:bg-black">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Record Payment</h3>
                
                <!-- Error Display -->
                <div v-if="paymentForm.errors.error" class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <p class="text-sm text-red-600 dark:text-red-400">{{ paymentForm.errors.error }}</p>
                </div>
                
                <form @submit.prevent="recordPayment">
                    <div class="space-y-4">
                        <!-- Subscription ID (hidden) -->
                        <input type="hidden" name="subscription_id" :value="subscription?.id" />
                        
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
                                    min="0.01"
                                    class="w-full px-3 py-2 pl-8 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white"
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
                            <select v-model="paymentForm.payment_method" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white" required>
                                <option value="">Select method</option>
                                <option value="cash">Cash</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="online">Online Payment</option>
                                <option value="other">Other</option>
                            </select>
                            <p v-if="paymentForm.errors.payment_method" class="mt-1 text-xs text-red-600">{{ paymentForm.errors.payment_method }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Payment Date
                            </label>
                            <input
                                v-model="paymentForm.payment_date"
                                type="date"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white"
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
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white"
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
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white"
                                placeholder="Any additional information..."
                            ></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-800">
                        <button
                            type="button"
                            @click="showPaymentModal = false"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-900"
                            :disabled="paymentForm.processing"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-gradient-to-r from-green-600 to-teal-600 text-white rounded-lg hover:shadow-lg disabled:opacity-50 flex items-center"
                            :disabled="paymentForm.processing"
                        >
                            <svg v-if="paymentForm.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ paymentForm.processing ? 'Recording...' : 'Record Payment' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Change Plan Modal -->
        <Modal :show="showChangePlanModal" @close="showChangePlanModal = false">
            <div class="p-6 bg-white dark:bg-black">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Change Plan</h3>
                <form @submit.prevent="changePlan">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">New Plan</label>
                            <select v-model="changePlanForm.plan_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg" required>
                                <option value="">Select Plan</option>
                                <option v-for="plan in availablePlans" :key="plan.id" :value="plan.id">
                                    {{ plan.plan_name }} - ${{ plan.price }}/{{ plan.billing_cycle }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Effective Date</label>
                            <select v-model="changePlanForm.effective_date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg">
                                <option value="immediate">Immediate</option>
                                <option value="next_renewal">Next Renewal</option>
                            </select>
                        </div>
                        <div v-if="changePlanForm.effective_date === 'immediate'" class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <p class="text-sm text-blue-700 dark:text-blue-400">
                                Prorated charges will be calculated based on remaining days.
                            </p>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" @click="showChangePlanModal = false" class="px-4 py-2 border border-gray-300 rounded-lg">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-gradient-to-r from-green-600 to-teal-600 text-white rounded-lg">Change Plan</button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Cancel Modal -->
        <Modal :show="showCancelModal" @close="showCancelModal = false">
            <div class="p-6 bg-white dark:bg-black">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 dark:bg-red-900/30 rounded-full mb-4">
                    <ExclamationTriangleIcon class="w-6 h-6 text-red-600 dark:text-red-400" />
                </div>
                
                <h3 class="text-lg font-medium text-gray-900 dark:text-white text-center mb-2">Cancel Subscription</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 text-center mb-6">
                    Are you sure you want to cancel this subscription? This action cannot be undone.
                </p>
                
                <select v-model="cancelReason" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg mb-3">
                    <option value="">Select a reason</option>
                    <option value="too_expensive">Too expensive</option>
                    <option value="not_using">Not using</option>
                    <option value="technical_issues">Technical issues</option>
                    <option value="switching_competitor">Switching to competitor</option>
                    <option value="other">Other</option>
                </select>
                
                <textarea
                    v-if="cancelReason === 'other'"
                    v-model="cancelReasonText"
                    placeholder="Please specify reason..."
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg mb-4"
                    rows="3"
                ></textarea>
                
                <div class="flex justify-end space-x-3">
                    <button @click="showCancelModal = false" class="px-4 py-2 border border-gray-300 rounded-lg">
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
            <div class="p-6 bg-white dark:bg-black">
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
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg mb-4"
                    rows="3"
                ></textarea>
                
                <div class="flex justify-end space-x-3">
                    <button @click="showSuspendModal = false" class="px-4 py-2 border border-gray-300 rounded-lg">
                        Cancel
                    </button>
                    <button @click="suspendSubscription" class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg">
                        Confirm Suspension
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Delete Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6 bg-white dark:bg-black">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 dark:bg-red-900/30 rounded-full mb-4">
                    <TrashIcon class="w-6 h-6 text-red-600 dark:text-red-400" />
                </div>
                
                <h3 class="text-lg font-medium text-gray-900 dark:text-white text-center mb-2">Delete Subscription</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 text-center mb-6">
                    Are you sure you want to delete this subscription? This action cannot be undone.
                </p>
                
                <div class="flex justify-end space-x-3">
                    <button @click="showDeleteModal = false" class="px-4 py-2 border border-gray-300 rounded-lg">
                        Cancel
                    </button>
                    <button @click="deleteSubscription" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                        Delete
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SubscriptionStatusBadge from '@/Components/Subscription/SubscriptionStatusBadge.vue'
import Modal from '@/Components/Modal.vue'
import { useColors } from '@/Composables/useColors'
import {
    ChevronRightIcon,
    UserIcon,
    CalendarIcon,
    XCircleIcon,
    PencilIcon,
    TrashIcon,
    CubeIcon,
    CurrencyDollarIcon,
    ClockIcon,
    BanknotesIcon,
    DocumentTextIcon,
    Cog6ToothIcon,
    PlusIcon,
    ArrowRightIcon,
    ArrowPathIcon,
    ArrowUpIcon,
    PauseIcon,
    PlayIcon,
    ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline'

const props = defineProps({
    subscription: Object,
    can: Object
})

const { getStatusColor, getStatusLabel } = useColors()

// Modal states
const showCancelModal = ref(false)
const showSuspendModal = ref(false)
const showDeleteModal = ref(false)
const showPaymentModal = ref(false)
const showChangePlanModal = ref(false)
const cancelReason = ref('')
const cancelReasonText = ref('')
const suspendReason = ref('')

// Available plans for change
const availablePlans = ref([])

// Payment form (FIXED: added subscription_id)
const paymentForm = useForm({
    subscription_id: props.subscription?.id,
    amount: props.subscription?.plan?.price || '',
    payment_method: '',
    payment_date: new Date().toISOString().split('T')[0],
    reference_number: '',
    notes: '',
})

// Change plan form
const changePlanForm = ref({
    plan_id: '',
    effective_date: 'next_renewal'
})

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

const totalPaid = computed(() => {
    return props.subscription?.payments?.reduce((sum, p) => sum + Number(p.amount), 0) || 0
})

const paymentProgress = computed(() => {
    const price = props.subscription?.plan?.price || 1
    return Math.min(Math.round((totalPaid.value / price) * 100), 100)
})

const paymentBannerClass = computed(() => {
    const map = {
        paid: 'bg-gradient-to-r from-green-600 to-green-700',
        unpaid: 'bg-gradient-to-r from-red-600 to-red-700',
        partial: 'bg-gradient-to-r from-yellow-600 to-yellow-700',
    }
    return map[props.subscription?.payment_status] || 'bg-gradient-to-r from-gray-600 to-gray-700'
})

const isExpiringSoon = computed(() => {
    if (!props.subscription?.end_date) return false
    const days = daysLeft(props.subscription.end_date)
    return days <= 7 && days > 0
})

const isTrialActive = computed(() => {
    if (!props.subscription?.trial_end_date) return false
    return new Date(props.subscription.trial_end_date) > new Date()
})

const isTrialExpired = computed(() => {
    if (!props.subscription?.trial_end_date) return false
    return new Date(props.subscription.trial_end_date) < new Date()
})

const needsApproval = computed(() => {
    // FR-SUB-06: Discounts above configured amount require approval
    const discountThreshold = 100 // Example threshold
    return props.subscription?.discount > discountThreshold
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

const daysLeft = (date) => {
    if (!date) return 0
    const end = new Date(date)
    const today = new Date()
    const diffTime = end - today
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24))
}

const getPaymentStatusLabel = (status) => {
    const map = {
        paid: 'Paid',
        unpaid: 'Unpaid',
        partial: 'Partial Payment'
    }
    return map[status] || status
}

const getPaymentRecordStatusClass = (status) => {
    const map = {
        completed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100',
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100',
        failed: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100',
        refunded: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
    }
    return map[status] || 'bg-gray-100 text-gray-800'
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
    const reason = cancelReason.value === 'other' ? cancelReasonText.value : cancelReason.value
    
    router.put(`/subscriptions/subscription/${props.subscription?.id}/cancel`, {
        reason: reason
    }, {
        onSuccess: () => {
            showCancelModal.value = false
            cancelReason.value = ''
            cancelReasonText.value = ''
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

const confirmDelete = () => {
    showDeleteModal.value = true
}

const deleteSubscription = () => {
    router.delete(`/subscriptions/subscription/${props.subscription?.id}`, {
        onSuccess: () => {
            showDeleteModal.value = false
            router.visit('/subscriptions')
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

// FIXED: Record payment function with proper error handling
const recordPayment = () => {
    // Ensure subscription_id is set
    paymentForm.subscription_id = props.subscription?.id
    
    paymentForm.post('/subscriptions/payments/record', {
        preserveScroll: true,
        onSuccess: (response) => {
            console.log('Payment success:', response)
            showPaymentModal.value = false
            paymentForm.reset()
            
            // Refresh the page data
            router.reload({
                only: ['subscription'],
                onSuccess: () => {
                    // You can add a toast notification here if you have one
                    alert('Payment recorded successfully!')
                }
            })
        },
        onError: (errors) => {
            console.error('Payment error:', errors)
            // Error is already displayed in the form
        },
        onFinish: () => {
            // Reset processing state
            paymentForm.processing = false
        }
    })
}

const voidPayment = (payment) => {
    if (confirm('Are you sure you want to void this payment?')) {
        router.post(`/subscriptions/payments/${payment.id}/void`, {}, {
            onSuccess: () => {
                router.reload({
                    only: ['subscription']
                })
            }
        })
    }
}

const changePlan = () => {
    router.post(`/subscriptions/subscription/${props.subscription?.id}/change-plan`, changePlanForm.value, {
        onSuccess: () => {
            showChangePlanModal.value = false
        }
    })
}

// Fetch available plans on mount
onMounted(() => {
    fetch('/subscriptions/plans/active/list')
        .then(res => res.json())
        .then(data => {
            availablePlans.value = data
        })
        .catch(err => console.error('Error fetching plans:', err))
})
</script>