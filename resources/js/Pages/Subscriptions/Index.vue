<template>
    <AuthenticatedLayout>
        <template #breadcrumbs>
            <div class="flex items-center space-x-2 text-sm">
                <Link href="/dashboard" class="text-gray-500 hover:text-teal-600 dark:text-gray-400 dark:hover:text-teal-400">Dashboard</Link>
                <ChevronRightIcon class="w-4 h-4 text-gray-400" />
                <span class="text-gray-900 dark:text-white font-medium">Subscriptions</span>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Simple Header -->
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Subscriptions</h2>
            </div>

            <!-- Three Tabs -->
            <div class="border-b border-gray-200 dark:border-gray-800">
                <nav class="flex space-x-8">
                    <!-- Tab 1: Plans -->
                    <button
                        @click="activeTab = 'plans'"
                        class="py-4 px-1 relative group"
                        :class="activeTab === 'plans' ? 'text-teal-600 dark:text-teal-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                    >
                        <div class="flex items-center space-x-2">
                            <CubeIcon class="w-5 h-5" />
                            <span class="font-medium">Plans</span>
                            <span v-if="plans.length" class="ml-2 px-2 py-0.5 text-xs bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-100 rounded-full">
                                {{ plans.length }}
                            </span>
                        </div>
                        <div v-if="activeTab === 'plans'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-teal-600 dark:bg-teal-400"></div>
                    </button>

                    <!-- Tab 2: Client Subscriptions -->
                    <button
                        @click="activeTab = 'client-subscriptions'"
                        class="py-4 px-1 relative group"
                        :class="activeTab === 'client-subscriptions' ? 'text-teal-600 dark:text-teal-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                    >
                        <div class="flex items-center space-x-2">
                            <UsersIcon class="w-5 h-5" />
                            <span class="font-medium">Client Subscriptions</span>
                            <span v-if="clientSubscriptions.length" class="ml-2 px-2 py-0.5 text-xs bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-100 rounded-full">
                                {{ clientSubscriptions.length }}
                            </span>
                        </div>
                        <div v-if="activeTab === 'client-subscriptions'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-teal-600 dark:bg-teal-400"></div>
                    </button>

                    <!-- Tab 3: Renewals Due -->
                    <button
                        @click="activeTab = 'renewals'"
                        class="py-4 px-1 relative group"
                        :class="activeTab === 'renewals' ? 'text-teal-600 dark:text-teal-400' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                    >
                        <div class="flex items-center space-x-2">
                            <ArrowPathIcon class="w-5 h-5" />
                            <span class="font-medium">Renewals Due</span>
                            <span v-if="renewals.length" class="ml-2 px-2 py-0.5 text-xs bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100 rounded-full">
                                {{ renewals.length }}
                            </span>
                        </div>
                        <div v-if="activeTab === 'renewals'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-teal-600 dark:bg-teal-400"></div>
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="mt-6">
                <!-- TAB 1: PLANS - WITH FULL CRUD -->
                <div v-if="activeTab === 'plans'" class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Plans</h3>
                        <button
                            @click="openCreateModal"
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-600 to-teal-600 hover:from-green-700 hover:to-teal-700 text-white rounded-lg transition-all duration-200 shadow-sm hover:shadow-md"
                        >
                            <PlusIcon class="w-5 h-5 mr-2" />
                            Create New Plan
                        </button>
                    </div>

                    <!-- Plans Filters -->
                    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <input v-model="planFilters.search" type="text" placeholder="Search plans..." 
                                   class="px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white">
                            <select v-model="planFilters.status" class="px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <div class="flex items-end">
                                <button @click="applyPlanFilters" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700">
                                    Apply Filters
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Plans Table with CRUD Actions -->
                    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Plan Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Billing</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Price</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Trial</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Active Subs</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                    <tr v-for="plan in filteredPlans" :key="plan.id" class="hover:bg-gray-50 dark:hover:bg-gray-900">
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-gray-900 dark:text-white">{{ plan.plan_name }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400 capitalize">{{ plan.billing_cycle }}</td>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">${{ plan.price }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ plan.trial_days }} days</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-100 rounded-full text-xs">
                                                {{ plan.active_subscriptions_count || 0 }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span :class="plan.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100'"
                                                  class="px-2 py-1 text-xs font-medium rounded-full">
                                                {{ plan.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center space-x-2">
                                                <button @click="viewPlan(plan)" class="p-1 text-teal-600 hover:text-teal-800" title="View">
                                                    <EyeIcon class="w-4 h-4" />
                                                </button>
                                                <button @click="editPlan(plan)" class="p-1 text-green-600 hover:text-green-800" title="Edit">
                                                    <PencilIcon class="w-4 h-4" />
                                                </button>
                                                <button @click="togglePlanStatus(plan)" 
                                                        class="p-1 text-yellow-600 hover:text-yellow-800" 
                                                        :title="plan.is_active ? 'Deactivate' : 'Activate'">
                                                    <PlayIcon v-if="!plan.is_active" class="w-4 h-4" />
                                                    <PauseIcon v-else class="w-4 h-4" />
                                                </button>
                                                <button @click="confirmDeletePlan(plan)" class="p-1 text-red-600 hover:text-red-800" title="Delete">
                                                    <TrashIcon class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="filteredPlans.length === 0">
                                        <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                            No plans found. Click "Create New Plan" to get started.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: CLIENT SUBSCRIPTIONS -->
                <div v-if="activeTab === 'client-subscriptions'" class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Client Subscriptions</h3>
                        <button
                            @click="showAssignModal = true"
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-600 to-teal-600 hover:from-green-700 hover:to-teal-700 text-white rounded-lg transition-all duration-200 shadow-sm hover:shadow-md"
                        >
                            <PlusIcon class="w-5 h-5 mr-2" />
                            Assign Subscription
                        </button>
                    </div>

                    <!-- Filters -->
                    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <input v-model="filters.search" type="text" placeholder="Search client or subscription..." 
                                   class="px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white">
                            <select v-model="filters.status" class="px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="trial">Trial</option>
                                <option value="past_due">Past Due</option>
                                <option value="suspended">Suspended</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            <select v-model="filters.plan_id" class="px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white">
                                <option value="">All Plans</option>
                                <option v-for="plan in plans" :key="plan.id" :value="plan.id">{{ plan.plan_name }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Client Subscriptions Table -->
                    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Subscription ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Client</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Plan</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Start Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">End Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Payment</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                    <tr v-for="sub in filteredSubscriptions" :key="sub.id" class="hover:bg-gray-50 dark:hover:bg-gray-900">
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ sub.subscription_id }}</td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-green-500 to-teal-500 flex items-center justify-center text-white text-sm font-medium mr-3">
                                                    {{ sub.client?.organization_name?.charAt(0) }}
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ sub.client?.organization_name }}</div>
                                                    <div class="text-xs text-gray-500">{{ sub.client?.email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ sub.plan?.plan_name }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ formatDate(sub.start_date) }}</td>
                                        <td class="px-6 py-4 text-sm" :class="isExpiringSoon(sub.end_date) ? 'text-red-600 font-medium' : 'text-gray-600 dark:text-gray-400'">
                                            {{ formatDate(sub.end_date) }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <SubscriptionStatusBadge :status="sub.status" />
                                        </td>
                                        <td class="px-6 py-4">
                                            <span :class="getPaymentStatusClass(sub.payment_status)" class="px-2 py-1 text-xs rounded-full">
                                                {{ sub.payment_status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <Link :href="`/subscriptions/subscription/${sub.id}`" class="p-1 text-teal-600 hover:text-teal-800">
                                                <EyeIcon class="w-4 h-4" />
                                            </Link>
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
                </div>

                <!-- TAB 3: RENEWALS DUE -->
                <div v-if="activeTab === 'renewals'" class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Renewals Due</h3>
                        <div class="flex items-center space-x-3">
                            <select v-model="renewalDays" @change="fetchRenewals" class="px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white">
                                <option value="7">Next 7 Days</option>
                                <option value="15">Next 15 Days</option>
                                <option value="30">Next 30 Days</option>
                                <option value="60">Next 60 Days</option>
                            </select>
                        </div>
                    </div>

                    <!-- Renewals Table -->
                    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Subscription ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Client</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Plan</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Renewal Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Days Left</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                    <tr v-for="renewal in renewals" :key="renewal.id" 
                                        class="hover:bg-gray-50 dark:hover:bg-gray-900"
                                        :class="{ 'bg-red-50 dark:bg-red-900/10': daysLeft(renewal.renewal_date) <= 3 }">
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ renewal.subscription_id }}</td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-green-500 to-teal-500 flex items-center justify-center text-white text-sm font-medium mr-3">
                                                    {{ renewal.client?.organization_name?.charAt(0) }}
                                                </div>
                                                <span class="text-sm text-gray-900 dark:text-white">{{ renewal.client?.organization_name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ renewal.plan?.plan_name }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ formatDate(renewal.renewal_date) }}</td>
                                        <td class="px-6 py-4">
                                            <span :class="getDaysLeftClass(renewal.renewal_date)" class="font-medium">
                                                {{ daysLeft(renewal.renewal_date) }} days
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">${{ renewal.plan?.price }}</td>
                                        <td class="px-6 py-4">
                                            <button @click="processRenewal(renewal)" 
                                                    class="px-3 py-1 bg-teal-600 hover:bg-teal-700 text-white text-sm rounded-lg transition-colors">
                                                Process Renewal
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="renewals.length === 0">
                                        <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                            No renewals due
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Plan Modal -->
        <Modal :show="showCreateModal" @close="closePlanModal" maxWidth="2xl">
            <div class="p-6 bg-white dark:bg-black">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">
                    {{ editingPlan ? 'Edit Plan' : 'Create New Plan' }}
                </h3>
                <form @submit.prevent="savePlan">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Plan Name</label>
                            <input v-model="planForm.plan_name" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white" required>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Price ($)</label>
                                <input v-model="planForm.price" type="number" step="0.01" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Billing Cycle</label>
                                <select v-model="planForm.billing_cycle" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white" required>
                                    <option value="monthly">Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Trial Days</label>
                                <input v-model="planForm.trial_days" type="number" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Support Level</label>
                                <select v-model="planForm.support_level" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white">
                                    <option value="standard">Standard</option>
                                    <option value="premium">Premium</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Max Users</label>
                                <input v-model="planForm.max_users" type="number" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white" placeholder="Unlimited">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Max Branches</label>
                                <input v-model="planForm.max_branches" type="number" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white" placeholder="Unlimited">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                            <textarea v-model="planForm.notes" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white"></textarea>
                        </div>
                        <div class="flex items-center">
                            <input v-model="planForm.is_active" type="checkbox" class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-gray-300 rounded">
                            <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</label>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" @click="closePlanModal" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-900">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-gradient-to-r from-green-600 to-teal-600 text-white rounded-lg hover:shadow-lg" :disabled="saving">
                            {{ saving ? 'Saving...' : (editingPlan ? 'Update Plan' : 'Create Plan') }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- View Plan Modal -->
        <Modal :show="showViewModal" @close="showViewModal = false" maxWidth="2xl">
            <div class="p-6 bg-white dark:bg-black">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Plan Details</h3>
                <div v-if="selectedPlan" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Plan Name</label>
                            <p class="text-lg font-medium text-gray-900 dark:text-white">{{ selectedPlan.plan_name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Price</label>
                            <p class="text-lg font-medium text-gray-900 dark:text-white">${{ selectedPlan.price }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Billing Cycle</label>
                            <p class="text-gray-900 dark:text-white capitalize">{{ selectedPlan.billing_cycle }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Trial Days</label>
                            <p class="text-gray-900 dark:text-white">{{ selectedPlan.trial_days }} days</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Support Level</label>
                            <p class="text-gray-900 dark:text-white capitalize">{{ selectedPlan.support_level }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Status</label>
                            <span :class="selectedPlan.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" 
                                  class="px-2 py-1 text-xs font-medium rounded-full">
                                {{ selectedPlan.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                    <div v-if="selectedPlan.max_users || selectedPlan.max_branches" class="grid grid-cols-2 gap-4">
                        <div v-if="selectedPlan.max_users">
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Max Users</label>
                            <p class="text-gray-900 dark:text-white">{{ selectedPlan.max_users }}</p>
                        </div>
                        <div v-if="selectedPlan.max_branches">
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Max Branches</label>
                            <p class="text-gray-900 dark:text-white">{{ selectedPlan.max_branches }}</p>
                        </div>
                    </div>
                    <div v-if="selectedPlan.notes">
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Notes</label>
                        <p class="text-gray-900 dark:text-white">{{ selectedPlan.notes }}</p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <button @click="showViewModal = false" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700">Close</button>
                </div>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6 bg-white dark:bg-black">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Delete Subscription Plan</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    Are you sure you want to delete "{{ selectedPlan?.plan_name }}"? This action cannot be undone.
                </p>
                <div class="flex justify-end space-x-3">
                    <button @click="showDeleteModal = false" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-700 dark:text-gray-300">
                        Cancel
                    </button>
                    <button @click="deletePlan" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg" :disabled="deleting">
                        {{ deleting ? 'Deleting...' : 'Delete Plan' }}
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Assign Subscription Modal -->
        <Modal :show="showAssignModal" @close="showAssignModal = false" maxWidth="2xl">
            <div class="p-6 bg-white dark:bg-black">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Assign Subscription to Client</h3>
                <form @submit.prevent="assignSubscription">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Client</label>
                            <select v-model="assignForm.client_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white" required>
                                <option value="">Select Client</option>
                                <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.organization_name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Plan</label>
                            <select v-model="assignForm.plan_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white" required>
                                <option value="">Select Plan</option>
                                <option v-for="plan in plans" :key="plan.id" :value="plan.id">{{ plan.plan_name }} - ${{ plan.price }}/{{ plan.billing_cycle }}</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                                <input v-model="assignForm.start_date" type="date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Method</label>
                                <select v-model="assignForm.payment_method" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white">
                                    <option value="">Select</option>
                                    <option value="cash">Cash</option>
                                    <option value="bank">Bank Transfer</option>
                                    <option value="online">Online</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" @click="showAssignModal = false" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-900">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-gradient-to-r from-green-600 to-teal-600 text-white rounded-lg hover:shadow-lg" :disabled="assigning">
                            {{ assigning ? 'Assigning...' : 'Assign Subscription' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'
import SubscriptionStatusBadge from '@/Components/Subscription/SubscriptionStatusBadge.vue'
import {
    ChevronRightIcon,
    CubeIcon,
    UsersIcon,
    ArrowPathIcon,
    PlusIcon,
    EyeIcon,
    PencilIcon,
    TrashIcon,
    PauseIcon,
    PlayIcon
} from '@heroicons/vue/24/outline'

// Tab state
const activeTab = ref('plans')

// Data
const plans = ref([])
const clientSubscriptions = ref([])
const renewals = ref([])
const clients = ref([])

// Loading states
const loading = ref({
    plans: false,
    subscriptions: false,
    renewals: false,
    clients: false
})

const saving = ref(false)
const deleting = ref(false)
const assigning = ref(false)

// Plan Filters
const planFilters = ref({
    search: '',
    status: ''
})

// Client Subscription Filters
const filters = ref({
    search: '',
    status: '',
    plan_id: ''
})

// Renewals filter
const renewalDays = ref(30)

// Modal states
const showCreateModal = ref(false)
const showViewModal = ref(false)
const showDeleteModal = ref(false)
const showAssignModal = ref(false)
const editingPlan = ref(null)
const selectedPlan = ref(null)

// Plan Form
const planForm = ref({
    plan_name: '',
    billing_cycle: 'monthly',
    price: '',
    trial_days: 0,
    support_level: 'standard',
    max_users: '',
    max_branches: '',
    notes: '',
    is_active: true
})

// Assign form
const assignForm = ref({
    client_id: '',
    plan_id: '',
    start_date: '',
    payment_method: ''
})

// Computed properties for filtered plans
const filteredPlans = computed(() => {
    return plans.value.filter(plan => {
        const matchesSearch = !planFilters.value.search || 
            plan.plan_name?.toLowerCase().includes(planFilters.value.search.toLowerCase())
        
        const matchesStatus = !planFilters.value.status || 
            (planFilters.value.status === 'active' ? plan.is_active : !plan.is_active)
        
        return matchesSearch && matchesStatus
    })
})

// Computed properties for filtered subscriptions
const filteredSubscriptions = computed(() => {
    return clientSubscriptions.value.filter(sub => {
        const matchesSearch = !filters.value.search || 
            sub.client?.organization_name?.toLowerCase().includes(filters.value.search.toLowerCase()) ||
            sub.subscription_id?.toLowerCase().includes(filters.value.search.toLowerCase())
        
        const matchesStatus = !filters.value.status || sub.status === filters.value.status
        const matchesPlan = !filters.value.plan_id || sub.plan_id === parseInt(filters.value.plan_id)
        
        return matchesSearch && matchesStatus && matchesPlan
    })
})

// Helper functions
const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

const daysLeft = (date) => {
    if (!date) return 0
    const diff = new Date(date) - new Date()
    return Math.ceil(diff / (1000 * 60 * 60 * 24))
}

const isExpiringSoon = (date) => {
    return daysLeft(date) <= 7 && daysLeft(date) > 0
}

const getDaysLeftClass = (date) => {
    const days = daysLeft(date)
    if (days <= 3) return 'text-red-600'
    if (days <= 7) return 'text-yellow-600'
    return 'text-green-600'
}

const getPaymentStatusClass = (status) => {
    const map = {
        paid: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100',
        unpaid: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100',
        partial: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100'
    }
    return map[status] || 'bg-gray-100 text-gray-800'
}

// Plan CRUD Operations
const applyPlanFilters = () => {
    // Filters are applied reactively through computed property
}

const viewPlan = (plan) => {
    selectedPlan.value = plan
    showViewModal.value = true
}

const editPlan = (plan) => {
    editingPlan.value = plan
    planForm.value = {
        plan_name: plan.plan_name,
        billing_cycle: plan.billing_cycle,
        price: plan.price,
        trial_days: plan.trial_days,
        support_level: plan.support_level || 'standard',
        max_users: plan.max_users,
        max_branches: plan.max_branches,
        notes: plan.notes || '',
        is_active: plan.is_active
    }
    showCreateModal.value = true
}

const togglePlanStatus = async (plan) => {
    try {
        const response = await fetch(`/subscriptions/plans/${plan.id}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        
        if (response.ok) {
            // Update local data
            plan.is_active = !plan.is_active
        } else {
            const errorText = await response.text()
            console.error('Failed to toggle status:', errorText)
        }
    } catch (error) {
        console.error('Error toggling plan status:', error)
    }
}

const confirmDeletePlan = (plan) => {
    selectedPlan.value = plan
    showDeleteModal.value = true
}

const deletePlan = async () => {
    if (!selectedPlan.value) return
    
    deleting.value = true
    try {
        const response = await fetch(`/subscriptions/plans/${selectedPlan.value.id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        
        if (response.ok) {
            // Remove from local array
            plans.value = plans.value.filter(p => p.id !== selectedPlan.value.id)
            showDeleteModal.value = false
            selectedPlan.value = null
        } else {
            const errorText = await response.text()
            console.error('Failed to delete plan:', errorText)
        }
    } catch (error) {
        console.error('Error deleting plan:', error)
    } finally {
        deleting.value = false
    }
}

const openCreateModal = () => {
    editingPlan.value = null
    planForm.value = {
        plan_name: '',
        billing_cycle: 'monthly',
        price: '',
        trial_days: 0,
        support_level: 'standard',
        max_users: '',
        max_branches: '',
        notes: '',
        is_active: true
    }
    showCreateModal.value = true
}

const closePlanModal = () => {
    showCreateModal.value = false
    editingPlan.value = null
}

const savePlan = async () => {
    saving.value = true
    try {
        const url = editingPlan.value 
            ? `/subscriptions/plans/${editingPlan.value.id}`
            : '/subscriptions/plans'
        
        const method = editingPlan.value ? 'PUT' : 'POST'
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify(planForm.value)
        })
        
        if (response.ok) {
            const savedPlan = await response.json()
            
            if (editingPlan.value) {
                // Update existing plan in array
                const index = plans.value.findIndex(p => p.id === editingPlan.value.id)
                plans.value[index] = savedPlan
            } else {
                // Add new plan to array
                plans.value.push(savedPlan)
            }
            
            closePlanModal()
        } else {
            const errorText = await response.text()
            console.error('Failed to save plan:', errorText)
        }
    } catch (error) {
        console.error('Error saving plan:', error)
    } finally {
        saving.value = false
    }
}

// API Calls for other data
const fetchPlans = async () => {
    loading.value.plans = true
    try {
        const response = await fetch('/subscriptions/plans?per_page=100', {
            headers: {
                'Accept': 'application/json'
            }
        })
        if (!response.ok) throw new Error('Failed to fetch plans')
        const data = await response.json()
        plans.value = data.data || data
    } catch (error) {
        console.error('Error fetching plans:', error)
        plans.value = []
    } finally {
        loading.value.plans = false
    }
}

const fetchClientSubscriptions = async () => {
    loading.value.subscriptions = true
    try {
        const response = await fetch('/subscriptions/client-subscriptions/all', {
            headers: {
                'Accept': 'application/json'
            }
        })
        if (!response.ok) throw new Error('Failed to fetch subscriptions')
        const data = await response.json()
        clientSubscriptions.value = data.data || data
    } catch (error) {
        console.error('Error fetching subscriptions:', error)
        clientSubscriptions.value = []
    } finally {
        loading.value.subscriptions = false
    }
}

const fetchRenewals = async () => {
    loading.value.renewals = true
    try {
        const response = await fetch(`/subscriptions/renewals/due/${renewalDays.value}`, {
            headers: {
                'Accept': 'application/json'
            }
        })
        if (!response.ok) throw new Error('Failed to fetch renewals')
        const data = await response.json()
        renewals.value = data.data || data
    } catch (error) {
        console.error('Error fetching renewals:', error)
        renewals.value = []
    } finally {
        loading.value.renewals = false
    }
}

const fetchClients = async () => {
    loading.value.clients = true
    try {
        const response = await fetch('/clients/list', {
            headers: {
                'Accept': 'application/json'
            }
        })
        if (!response.ok) throw new Error('Failed to fetch clients')
        const data = await response.json()
        clients.value = data
    } catch (error) {
        console.error('Error fetching clients:', error)
        clients.value = []
    } finally {
        loading.value.clients = false
    }
}

const assignSubscription = async () => {
    assigning.value = true
    try {
        const response = await fetch('/subscriptions/assign', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify(assignForm.value)
        })
        
        if (response.ok) {
            showAssignModal.value = false
            assignForm.value = { client_id: '', plan_id: '', start_date: '', payment_method: '' }
            fetchClientSubscriptions()
        } else {
            const errorText = await response.text()
            console.error('Failed to assign subscription:', errorText)
        }
    } catch (error) {
        console.error('Error assigning subscription:', error)
    } finally {
        assigning.value = false
    }
}

const processRenewal = async (renewal) => {
    if (confirm(`Process renewal for ${renewal.client?.organization_name}?`)) {
        try {
            const response = await fetch(`/subscriptions/renewals/${renewal.id}/process`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            if (response.ok) {
                fetchRenewals()
            } else {
                const errorText = await response.text()
                console.error('Failed to process renewal:', errorText)
            }
        } catch (error) {
            console.error('Error processing renewal:', error)
        }
    }
}

// Initialize
onMounted(() => {
    fetchPlans()
    fetchClientSubscriptions()
    fetchRenewals()
    fetchClients()
})
</script>