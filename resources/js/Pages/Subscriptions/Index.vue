<template>
    <AuthenticatedLayout>
        <template #breadcrumbs>
            <div class="flex items-center space-x-2 text-sm">
                <Link href="/dashboard" class="text-gray-500 hover:text-teal-600 dark:text-gray-400 dark:hover:text-teal-400 transition-colors">Dashboard</Link>
                <ChevronRightIcon class="w-4 h-4 text-gray-400 dark:text-gray-500" />
                <span class="text-gray-900 dark:text-white font-medium">Subscriptions</span>
            </div>
        </template>

        <!-- Pending Approvals Banner -->
        <div v-if="pendingApprovalsCount > 0" class="mb-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <ExclamationCircleIcon class="w-6 h-6 text-yellow-600 dark:text-yellow-400" />
                    <p class="text-yellow-700 dark:text-yellow-300">
                        <span class="font-bold">{{ pendingApprovalsCount }}</span> discount {{ pendingApprovalsCount === 1 ? 'request' : 'requests' }} pending approval
                    </p>
                </div>
                <Link :href="route('subscriptions.discount-approvals.pending')" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 text-sm">
                    Review Approvals
                </Link>
            </div>
        </div>

        <!-- Error Display -->
        <div v-if="pageError" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-4">
            <p class="text-red-600 dark:text-red-400">{{ pageError }}</p>
        </div>

        <!-- Loading Indicator -->
        <div v-if="loading.global" class="fixed top-0 left-0 w-full h-1 bg-teal-500 animate-pulse z-50"></div>

        <div class="space-y-6">
            <!-- Three Tabs -->
            <div class="pt-2">
                <nav class="flex space-x-8">
                    <!-- Tab 1: Plans -->
                    <button
                        @click="activeTab = 'plans'"
                        class="py-3 px-1 transition-all duration-200"
                        :class="activeTab === 'plans' 
                            ? 'text-teal-600 dark:text-teal-400 font-bold scale-105' 
                            : 'text-gray-600 dark:text-gray-300 font-medium hover:text-teal-600 dark:hover:text-teal-400'"
                    >
                        <div class="flex items-center space-x-2">
                            <CubeIcon class="w-5 h-5" :class="activeTab === 'plans' ? 'text-teal-600 dark:text-teal-400' : 'text-gray-500 dark:text-gray-400'" />
                            <span class="text-base">Plans</span>
                            <span v-if="plans?.length" 
                                  class="ml-1 px-2 py-0.5 text-xs rounded-full font-medium"
                                  :class="activeTab === 'plans' 
                                      ? 'bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-200' 
                                      : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300'">
                                {{ plans.length }}
                            </span>
                        </div>
                    </button>

                    <!-- Tab 2: Client Subscriptions -->
                    <button
                        @click="activeTab = 'client-subscriptions'"
                        class="py-3 px-1 transition-all duration-200"
                        :class="activeTab === 'client-subscriptions' 
                            ? 'text-teal-600 dark:text-teal-400 font-bold scale-105' 
                            : 'text-gray-600 dark:text-gray-300 font-medium hover:text-teal-600 dark:hover:text-teal-400'"
                    >
                        <div class="flex items-center space-x-2">
                            <UsersIcon class="w-5 h-5" :class="activeTab === 'client-subscriptions' ? 'text-teal-600 dark:text-teal-400' : 'text-gray-500 dark:text-gray-400'" />
                            <span class="text-base">Client Subscriptions</span>
                            <span v-if="clientSubscriptions?.length" 
                                  class="ml-1 px-2 py-0.5 text-xs rounded-full font-medium"
                                  :class="activeTab === 'client-subscriptions' 
                                      ? 'bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-200' 
                                      : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300'">
                                {{ clientSubscriptions.length }}
                            </span>
                        </div>
                    </button>

                    <!-- Tab 3: Renewals Due -->
                    <button
                        @click="activeTab = 'renewals'"
                        class="py-3 px-1 transition-all duration-200"
                        :class="activeTab === 'renewals' 
                            ? 'text-teal-600 dark:text-teal-400 font-bold scale-105' 
                            : 'text-gray-600 dark:text-gray-300 font-medium hover:text-teal-600 dark:hover:text-teal-400'"
                    >
                        <div class="flex items-center space-x-2">
                            <ArrowPathIcon class="w-5 h-5" :class="activeTab === 'renewals' ? 'text-teal-600 dark:text-teal-400' : 'text-gray-500 dark:text-gray-400'" />
                            <span class="text-base">Renewals Due</span>
                            <span v-if="renewals?.length" 
                                  class="ml-1 px-2 py-0.5 text-xs rounded-full font-medium"
                                  :class="activeTab === 'renewals' 
                                      ? 'bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-200' 
                                      : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300'">
                                {{ renewals.length }}
                            </span>
                        </div>
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="mt-6">
                <!-- TAB 1: PLANS -->
                <div v-if="activeTab === 'plans'" class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Plans</h3>
                        <button
                            @click="openCreateModal"
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-600 to-teal-600 hover:from-green-700 hover:to-teal-700 text-white rounded-lg transition-all duration-200 shadow-sm hover:shadow-md font-medium"
                        >
                            <PlusIcon class="w-5 h-5 mr-2" />
                            Create New Plan
                        </button>
                    </div>

                    <!-- Plans Filters -->
                    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <input v-model="planFilters.search" type="text" placeholder="Search plans..." 
                                   class="px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white dark:placeholder-gray-400">
                            <select v-model="planFilters.status" class="px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <div class="flex items-end">
                                <button @click="applyPlanFilters" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 font-medium">
                                    Apply Filters
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Plans Table -->
                    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-green-800">
                                <thead class="bg-gray-50 dark:bg-green-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-green-300 uppercase tracking-wider">Plan Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-green-300 uppercase tracking-wider">Billing</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-green-300 uppercase tracking-wider">Price</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-green-300 uppercase tracking-wider">Trial</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-green-300 uppercase tracking-wider">Active Subs</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-green-300 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-green-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-green-950 divide-y divide-gray-200 dark:divide-green-800">
                                    <tr v-for="plan in filteredPlans" :key="plan?.id" class="hover:bg-gray-50 dark:hover:bg-green-900 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-gray-900 dark:text-green-100">{{ plan?.plan_name || 'N/A' }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-green-200 capitalize">{{ plan?.billing_cycle || 'N/A' }}</td>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-green-100">${{ plan?.price || 0 }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-green-200">{{ plan?.trial_days || 0 }} days</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 bg-teal-100 text-teal-800 dark:bg-teal-800 dark:text-teal-100 rounded-full text-xs font-medium">
                                                {{ plan?.active_subscriptions_count || 0 }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span :class="plan?.is_active ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'"
                                                  class="px-2 py-1 text-xs font-medium rounded-full">
                                                {{ plan?.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center space-x-2">
                                                <Link :href="`/subscriptions/plans/${plan?.id}`" class="p-1 text-teal-600 hover:text-teal-800 dark:text-teal-400 dark:hover:text-teal-300" title="View Details">
                                                    <EyeIcon class="w-4 h-4" />
                                                </Link>
                                                <button @click="editPlan(plan)" class="p-1 text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300" title="Edit">
                                                    <PencilIcon class="w-4 h-4" />
                                                </button>
                                                <button @click="togglePlanStatus(plan)" 
                                                        class="p-1 text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-300" 
                                                        :title="plan?.is_active ? 'Deactivate' : 'Activate'">
                                                    <PlayIcon v-if="!plan?.is_active" class="w-4 h-4" />
                                                    <PauseIcon v-else class="w-4 h-4" />
                                                </button>
                                                <button @click="confirmDeletePlan(plan)" class="p-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Delete">
                                                    <TrashIcon class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!filteredPlans?.length">
                                        <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-green-300 bg-white dark:bg-green-950">
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
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-600 to-teal-600 hover:from-green-700 hover:to-teal-700 text-white rounded-lg transition-all duration-200 shadow-sm hover:shadow-md font-medium"
                        >
                            <PlusIcon class="w-5 h-5 mr-2" />
                            Assign Subscription
                        </button>
                    </div>

                    <!-- Filters -->
                    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <input v-model="filters.search" type="text" placeholder="Search client or subscription..." 
                                   class="px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white dark:placeholder-gray-400">
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
                                <option v-for="plan in plans" :key="plan?.id" :value="plan?.id">{{ plan?.plan_name }}</option>
                            </select>
                        </div>
                        <div class="mt-2 flex justify-end">
                            <button @click="applySubscriptionFilters" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 font-medium text-sm">
                                Apply Filters
                            </button>
                        </div>
                    </div>

                    <!-- Client Subscriptions Table -->
                    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-green-800">
                                <thead class="bg-gray-50 dark:bg-green-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-green-300 uppercase tracking-wider">Subscription ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-green-300 uppercase tracking-wider">Client</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-green-300 uppercase tracking-wider">Plan</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-green-300 uppercase tracking-wider">Start Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-green-300 uppercase tracking-wider">End Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-green-300 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-green-300 uppercase tracking-wider">Discount</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 dark:text-green-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-green-950 divide-y divide-gray-200 dark:divide-green-800">
                                    <tr v-for="sub in filteredSubscriptions" :key="sub?.id" class="hover:bg-gray-50 dark:hover:bg-green-900 transition-colors">
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-green-100">{{ sub?.subscription_id || 'N/A' }}</td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-green-500 to-teal-500 flex items-center justify-center text-white text-sm font-medium mr-3">
                                                    {{ sub?.client?.organization_name?.charAt(0) || '?' }}
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900 dark:text-green-100">{{ sub?.client?.organization_name || 'N/A' }}</div>
                                                    <div class="text-xs text-gray-500 dark:text-green-300">{{ sub?.client?.primary_contact_email || 'No email' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-green-200">{{ sub?.plan?.plan_name || 'N/A' }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-green-200">{{ formatDate(sub?.start_date) }}</td>
                                        <td class="px-6 py-4 text-sm" :class="isExpiringSoon(sub?.end_date) ? 'text-red-600 dark:text-red-400 font-medium' : 'text-gray-600 dark:text-green-200'">
                                            {{ formatDate(sub?.end_date) }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <SubscriptionStatusBadge :status="sub?.status" />
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col space-y-1">
                                                <!-- Show discount amount -->
                                                <div v-if="sub?.discount && sub?.discount > 0" 
                                                     :class="{
                                                         'text-green-600 dark:text-green-400': sub?.discount_status === 'approved',
                                                         'text-yellow-600 dark:text-yellow-400': sub?.discount_status === 'pending_approval',
                                                         'text-red-600 dark:text-red-400': sub?.discount_status === 'rejected',
                                                         'text-gray-600 dark:text-gray-400': !sub?.discount_status || sub?.discount_status === 'none'
                                                     }"
                                                     class="font-medium">
                                                    ${{ sub.discount }} off
                                                </div>
                                                <div v-else-if="sub?.pending_discount" 
                                                     :class="{
                                                         'text-yellow-600 dark:text-yellow-400': sub?.discount_status === 'pending_approval',
                                                         'text-red-600 dark:text-red-400': sub?.discount_status === 'rejected'
                                                     }"
                                                     class="font-medium">
                                                    ${{ sub.pending_discount }} off
                                                </div>
                                                <span v-else class="text-sm text-gray-400 dark:text-gray-500">No discount</span>
                                                
                                                <!-- Status Badge -->
                                                <span v-if="sub?.discount_status === 'approved'" 
                                                      class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 w-fit">
                                                    Approved
                                                </span>
                                                <span v-else-if="sub?.discount_status === 'pending_approval'" 
                                                      class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300 w-fit">
                                                    Pending
                                                </span>
                                                <span v-else-if="sub?.discount_status === 'rejected'" 
                                                      class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 w-fit">
                                                    Rejected
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center space-x-2">
                                                <Link :href="`/subscriptions/subscription/${sub?.id}`" class="p-1 text-teal-600 hover:text-teal-800 dark:text-teal-400 dark:hover:text-teal-300" title="View">
                                                    <EyeIcon class="w-4 h-4" />
                                                </Link>
                                                <button @click="editSubscription(sub)" class="p-1 text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300" title="Edit">
                                                    <PencilIcon class="w-4 h-4" />
                                                </button>
                                                <button @click="confirmDeleteSubscription(sub)" class="p-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" title="Delete">
                                                    <TrashIcon class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!filteredSubscriptions?.length">
                                        <td colspan="8" class="px-6 py-8 text-center text-gray-500 dark:text-green-300 bg-white dark:bg-green-950">
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
                    <!-- Header with filter -->
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Renewals Due</h3>
                        <div class="flex items-center space-x-3">
                            <select 
                                v-model="renewalDays" 
                                @change="fetchRenewals"
                                class="px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white"
                            >
                                <option value="7">Next 7 Days</option>
                                <option value="15">Next 15 Days</option>
                                <option value="30">Next 30 Days</option>
                                <option value="60">Next 60 Days</option>
                                <option value="90">Next 90 Days</option>
                                <option value="120">Next 120 Days</option>
                                <option value="365">Next 365 Days</option>
                                <option value="999">All Reminders</option>
                            </select>
                        </div>
                    </div>

                    <!-- Loading State for Renewals -->
                    <div v-if="loading.renewals" class="flex justify-center items-center py-12">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-teal-600"></div>
                    </div>

                    <!-- Stats Cards - Based on filtered renewals -->
                    <div v-else class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                        <div class="bg-red-50 dark:bg-red-950/30 rounded-xl shadow-sm border border-red-100 dark:border-red-900 p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-red-600 dark:text-red-400 text-sm font-medium">Due Today</p>
                                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ dueToday }}</p>
                                </div>
                                <div class="p-3 bg-red-100 dark:bg-red-900 rounded-full">
                                    <ExclamationCircleIcon class="w-6 h-6 text-red-600 dark:text-red-300" />
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-yellow-50 dark:bg-yellow-950/30 rounded-xl shadow-sm border border-yellow-100 dark:border-yellow-900 p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-yellow-600 dark:text-yellow-400 text-sm font-medium">This Week</p>
                                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ dueThisWeek }}</p>
                                </div>
                                <div class="p-3 bg-yellow-100 dark:bg-yellow-900 rounded-full">
                                    <ClockIcon class="w-6 h-6 text-yellow-600 dark:text-yellow-300" />
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-orange-50 dark:bg-orange-950/30 rounded-xl shadow-sm border border-orange-100 dark:border-orange-900 p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-orange-600 dark:text-orange-400 text-sm font-medium">This Month</p>
                                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ dueThisMonth }}</p>
                                </div>
                                <div class="p-3 bg-orange-100 dark:bg-orange-900 rounded-full">
                                    <CalendarIcon class="w-6 h-6 text-orange-600 dark:text-orange-300" />
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-teal-50 dark:bg-teal-950/30 rounded-xl shadow-sm border border-teal-100 dark:border-teal-900 p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-teal-600 dark:text-teal-400 text-sm font-medium">Total Revenue</p>
                                    <p class="text-3xl font-bold text-gray-900 dark:text-white">${{ totalRenewalAmount }}</p>
                                </div>
                                <div class="p-3 bg-teal-100 dark:bg-teal-900 rounded-full">
                                    <CurrencyDollarIcon class="w-6 h-6 text-teal-600 dark:text-teal-300" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Error State -->
                    <div v-if="renewalError" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6 text-center">
                        <ExclamationCircleIcon class="w-12 h-12 mx-auto text-red-500 mb-3" />
                        <h3 class="text-lg font-medium text-red-800 dark:text-red-200 mb-2">Error Loading Renewals</h3>
                        <p class="text-red-600 dark:text-red-400">{{ renewalError }}</p>
                        <button @click="fetchRenewals" class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            Try Again
                        </button>
                    </div>

                    <!-- Renewals List - Shows filtered data -->
                    <div v-else class="space-y-4">
                        <div v-for="renewal in renewals" :key="renewal?.id" 
                             class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden hover:shadow-md transition-all duration-200"
                             :class="{ 'border-l-4 border-l-red-400 dark:border-l-red-600': daysLeft(renewal?.renewal_date) <= 3 }">
                            
                            <div class="p-6 bg-white dark:bg-green-950">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-start space-x-4">
                                        <!-- Client Avatar -->
                                        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-green-400 to-teal-400 flex items-center justify-center text-white text-xl font-bold">
                                            {{ renewal?.client?.organization_name?.charAt(0) || '?' }}
                                        </div>
                                        
                                        <!-- Client & Subscription Info -->
                                        <div>
                                            <h4 class="text-lg font-semibold text-gray-900 dark:text-green-100">
                                                {{ renewal?.client?.organization_name || 'Unknown Client' }}
                                            </h4>
                                            <div class="flex items-center space-x-3 mt-1">
                                                <span class="text-sm text-gray-500 dark:text-green-300">
                                                    {{ renewal?.subscription_id }}
                                                </span>
                                                <span class="text-gray-300 dark:text-green-700">|</span>
                                                <span class="text-sm text-teal-600 dark:text-teal-400 font-medium">
                                                    {{ renewal?.plan?.plan_name || 'Unknown Plan' }}
                                                </span>
                                            </div>
                                            
                                            <!-- Contact Info -->
                                            <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500 dark:text-green-300">
                                                <span class="flex items-center">
                                                    <EnvelopeIcon class="w-3 h-3 mr-1" />
                                                    {{ renewal?.client?.primary_contact_email || 'No email' }}
                                                </span>
                                                <span class="flex items-center">
                                                    <PhoneIcon class="w-3 h-3 mr-1" />
                                                    {{ renewal?.client?.primary_contact_phone || 'No phone' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Days Left Badge -->
                                    <div class="text-right">
                                        <div class="px-3 py-1 rounded-full text-sm font-medium"
                                             :class="getDaysLeftBadgeClass(renewal?.renewal_date)">
                                            {{ daysLeft(renewal?.renewal_date) }} days left
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-green-300 mt-1">
                                            Renews: {{ formatDate(renewal?.renewal_date) }}
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Progress Bar -->
                                <div class="mt-4">
                                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-green-300 mb-1">
                                        <span>Time remaining</span>
                                        <span>{{ getProgressPercentage(renewal?.renewal_date) }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-2">
                                        <div 
                                            class="h-2 rounded-full transition-all duration-500"
                                            :class="getProgressBarClass(renewal?.renewal_date)"
                                            :style="{ width: getProgressPercentage(renewal?.renewal_date) + '%' }"
                                        ></div>
                                    </div>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="mt-4 flex items-center justify-end space-x-3 pt-4 border-t border-gray-100 dark:border-green-800">
                                    <Link :href="`/subscriptions/subscription/${renewal?.id}`" 
                                          class="px-4 py-2 text-gray-600 dark:text-green-300 hover:bg-gray-50 dark:hover:bg-green-900 rounded-lg transition-colors text-sm font-medium">
                                        View Details
                                    </Link>
                                    <button 
                                        @click="processRenewal(renewal)" 
                                        :disabled="renewal?.processing"
                                        class="px-4 py-2 bg-gradient-to-r from-green-500 to-teal-500 hover:from-green-600 hover:to-teal-600 text-white rounded-lg transition-all duration-200 shadow-sm hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed flex items-center space-x-2 font-medium"
                                    >
                                        <ArrowPathIcon class="w-4 h-4" :class="{ 'animate-spin': renewal?.processing }" />
                                        <span>{{ renewal?.processing ? 'Processing...' : 'Process Renewal' }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Empty State -->
                        <div v-if="!renewals?.length" class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-12 text-center">
                            <div class="w-20 h-20 mx-auto bg-gray-50 dark:bg-green-900 rounded-full flex items-center justify-center mb-4">
                                <CheckCircleIcon class="w-10 h-10 text-gray-400 dark:text-green-300" />
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-green-100 mb-2">No Renewals Due</h3>
                            <p class="text-gray-500 dark:text-green-300">There are no renewal reminders for the selected period.</p>
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
                            <input v-model="planForm.is_active" type="checkbox" class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-gray-300 dark:border-gray-700 rounded">
                            <label class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</label>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" @click="closePlanModal" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-900 font-medium">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-gradient-to-r from-green-600 to-teal-600 text-white rounded-lg hover:shadow-lg font-medium" :disabled="saving">
                            {{ saving ? 'Saving...' : (editingPlan ? 'Update Plan' : 'Create Plan') }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Subscription Modal -->
        <Modal :show="showEditSubscriptionModal" @close="closeEditSubscriptionModal" maxWidth="2xl">
            <div class="p-6 bg-white dark:bg-black">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Edit Subscription</h3>
                <form @submit.prevent="updateSubscription">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Client</label>
                            <select v-model.number="editSubscriptionForm.client_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white" required>
                                <option :value="null">Select Client</option>
                                <option v-for="client in clients" :key="client?.id" :value="client?.id">{{ client?.organization_name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Plan</label>
                            <select v-model.number="editSubscriptionForm.plan_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white" required>
                                <option :value="null">Select Plan</option>
                                <option v-for="plan in plans" :key="plan?.id" :value="plan?.id">{{ plan?.plan_name }} - ${{ plan?.price }}/{{ plan?.billing_cycle }}</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date</label>
                                <input v-model="editSubscriptionForm.start_date" type="date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date</label>
                                <input v-model="editSubscriptionForm.end_date" type="date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white" required>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                                <select v-model="editSubscriptionForm.status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white" required>
                                    <option value="active">Active</option>
                                    <option value="trial">Trial</option>
                                    <option value="past_due">Past Due</option>
                                    <option value="suspended">Suspended</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Method</label>
                                <select v-model="editSubscriptionForm.payment_method" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white">
                                    <option value="">Select</option>
                                    <option value="cash">Cash</option>
                                    <option value="bank">Bank Transfer</option>
                                    <option value="online">Online</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Status</label>
                            <select v-model="editSubscriptionForm.payment_status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white">
                                <option value="paid">Paid</option>
                                <option value="unpaid">Unpaid</option>
                                <option value="partial">Partial</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Discount Amount ($)</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">$</span>
                                <input 
                                    v-model.number="editSubscriptionForm.discount" 
                                    type="number" 
                                    step="0.01" 
                                    min="0"
                                    class="w-full px-3 py-2 pl-8 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white"
                                    placeholder="0.00"
                                />
                            </div>
                        </div>
                        <div v-if="editSubscriptionForm.discount > 0">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Discount Reason</label>
                            <textarea 
                                v-model="editSubscriptionForm.discount_reason" 
                                rows="2"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white"
                                placeholder="Reason for discount..."></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" @click="closeEditSubscriptionModal" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-900 font-medium">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-gradient-to-r from-green-600 to-teal-600 text-white rounded-lg hover:shadow-lg font-medium" :disabled="updating">
                            {{ updating ? 'Updating...' : 'Update Subscription' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Subscription Confirmation Modal -->
        <Modal :show="showDeleteSubscriptionModal" @close="showDeleteSubscriptionModal = false">
            <div class="p-6 bg-white dark:bg-black">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Delete Subscription</h3>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    Are you sure you want to delete subscription "{{ selectedSubscription?.subscription_id }}"? This action cannot be undone.
                </p>
                <div class="flex justify-end space-x-3">
                    <button @click="showDeleteSubscriptionModal = false" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-900 font-medium">
                        Cancel
                    </button>
                    <button @click="deleteSubscription" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium" :disabled="deletingSubscription">
                        {{ deletingSubscription ? 'Deleting...' : 'Delete Subscription' }}
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Assign Subscription Modal - UPDATED with status field and discount fields -->
        <Modal :show="showAssignModal" @close="showAssignModal = false" maxWidth="2xl">
            <div class="p-6 bg-white dark:bg-black">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Assign Subscription to Client</h3>

                <form @submit.prevent="assignSubscription">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Client</label>
                            <select v-model.number="assignForm.client_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white" required>
                                <option :value="null">Select Client</option>
                                <option v-for="client in clients" :key="client.id" :value="client.id">
                                    {{ client.organization_name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Plan</label>
                            <select v-model.number="assignForm.plan_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white" required>
                                <option :value="null">Select Plan</option>
                                <option v-for="plan in plans" :key="plan.id" :value="plan.id">
                                    {{ plan.plan_name }} - ${{ plan.price }}/{{ plan.billing_cycle }}
                                </option>
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
                        
                        <!-- Status Field -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Initial Status</label>
                            <select v-model="assignForm.status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white">
                                <option value="active">Active</option>
                                <option value="trial">Trial</option>
                                <option value="pending">Pending</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                        
                        <!-- Discount Fields -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-2">
                            <h4 class="text-md font-medium text-gray-900 dark:text-white mb-3">Discount (Optional)</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Discount Amount ($)</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">$</span>
                                        <input 
                                            v-model.number="assignForm.discount" 
                                            type="number" 
                                            step="0.01" 
                                            min="0"
                                            class="w-full px-3 py-2 pl-8 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white"
                                            placeholder="0.00"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Discount Reason</label>
                                    <input 
                                        v-model="assignForm.discount_reason" 
                                        type="text" 
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 dark:bg-gray-900 dark:text-white"
                                        placeholder="Reason for discount"
                                    />
                                </div>
                            </div>
                            <p v-if="assignForm.discount > 0" class="mt-2 text-xs text-yellow-600 dark:text-yellow-400">
                                Note: Discounts over $100 or 20% will require approval.
                            </p>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" @click="showAssignModal = false" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-900 font-medium">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-gradient-to-r from-green-600 to-teal-600 text-white rounded-lg hover:shadow-lg font-medium" :disabled="assigning">
                            {{ assigning ? 'Assigning...' : 'Assign Subscription' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Plan Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6 bg-white dark:bg-black">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Delete Subscription Plan</h3>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    Are you sure you want to delete "{{ selectedPlan?.plan_name }}"? This action cannot be undone.
                </p>
                <div class="flex justify-end space-x-3">
                    <button @click="showDeleteModal = false" class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-900 font-medium">
                        Cancel
                    </button>
                    <button @click="deletePlan" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium" :disabled="deleting">
                        {{ deleting ? 'Deleting...' : 'Delete Plan' }}
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
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
    PlayIcon,
    ExclamationCircleIcon,
    ClockIcon,
    CalendarIcon,
    CurrencyDollarIcon,
    EnvelopeIcon,
    PhoneIcon,
    CheckCircleIcon
} from '@heroicons/vue/24/outline'

// Get page props
const page = usePage()

// Define props with defaults
const props = defineProps({
    plans: {
        type: Array,
        default: () => []
    },
    clientSubscriptions: {
        type: Array,
        default: () => []
    },
    renewals: {
        type: Array,
        default: () => []
    },
    clients: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    error: {
        type: String,
        default: null
    },
    pendingApprovalsCount: {
        type: Number,
        default: 0
    }
})

// Page error
const pageError = ref(props.error)

// Pending approvals count
const pendingApprovalsCount = ref(props.pendingApprovalsCount || 0)

// Tab state - initialize from localStorage or default to 'plans'
const activeTab = ref(localStorage.getItem('subscriptionsActiveTab') || 'plans')

// Watch tab changes and save to localStorage
watch(activeTab, (newTab) => {
    localStorage.setItem('subscriptionsActiveTab', newTab)
})

// Local reactive data - initialize with props
const plans = ref([])
const clientSubscriptions = ref([])
const renewals = ref([])
const clients = ref([])

// Renewal error state
const renewalError = ref(null)

// Loading states
const loading = ref({
    global: false,
    plans: false,
    subscriptions: false,
    renewals: false,
    clients: false
})

const saving = ref(false)
const deleting = ref(false)
const assigning = ref(false)
const updating = ref(false)
const deletingSubscription = ref(false)

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

// Renewals filter - initialize from localStorage or default to 30
const renewalDays = ref(parseInt(localStorage.getItem('subscriptionsRenewalDays') || '30'))

// Watch renewalDays changes and save to localStorage
watch(renewalDays, (newDays) => {
    localStorage.setItem('subscriptionsRenewalDays', newDays.toString())
})

// Modal states
const showCreateModal = ref(false)
const showDeleteModal = ref(false)
const showAssignModal = ref(false)
const showEditSubscriptionModal = ref(false)
const showDeleteSubscriptionModal = ref(false)
const editingPlan = ref(null)
const selectedPlan = ref(null)
const selectedSubscription = ref(null)

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

// Edit Subscription Form - UPDATED with discount fields
const editSubscriptionForm = ref({
    id: null,
    client_id: null,
    plan_id: null,
    start_date: '',
    end_date: '',
    status: 'active',
    payment_method: '',
    payment_status: 'unpaid',
    discount: 0,
    discount_reason: ''
})

// Assign form - UPDATED with status field and discount fields
const assignForm = ref({
    client_id: null,
    plan_id: null,
    start_date: '',
    payment_method: '',
    status: 'active',
    discount: 0,
    discount_reason: ''
})

// Initialize data from props
const initializeData = () => {
    try {
        plans.value = Array.isArray(props.plans) ? props.plans : []
        clientSubscriptions.value = Array.isArray(props.clientSubscriptions) ? props.clientSubscriptions : []
        clients.value = Array.isArray(props.clients) ? props.clients : []
        
        // Handle renewals with processing flag - only if provided
        if (Array.isArray(props.renewals)) {
            renewals.value = props.renewals.map(r => ({ ...r, processing: false }))
        }
        
        // Update pending approvals count
        if (props.pendingApprovalsCount !== undefined) {
            pendingApprovalsCount.value = props.pendingApprovalsCount
        }
        
        console.log('Data initialized:', {
            plans: plans.value.length,
            subscriptions: clientSubscriptions.value.length,
            renewals: renewals.value.length,
            clients: clients.value.length,
            renewalDays: renewalDays.value,
            pendingApprovals: pendingApprovalsCount.value
        })
    } catch (err) {
        console.error('Error initializing data:', err)
        pageError.value = 'Error loading data: ' + err.message
    }
}

// Watch for props changes
watch(() => props, () => {
    try {
        plans.value = Array.isArray(props.plans) ? props.plans : []
        clientSubscriptions.value = Array.isArray(props.clientSubscriptions) ? props.clientSubscriptions : []
        clients.value = Array.isArray(props.clients) ? props.clients : []
        
        // Handle renewals with processing flag - only if provided
        if (Array.isArray(props.renewals)) {
            renewals.value = props.renewals.map(r => ({ ...r, processing: false }))
        }
        
        // Update pending approvals count
        if (props.pendingApprovalsCount !== undefined) {
            pendingApprovalsCount.value = props.pendingApprovalsCount
        }
        
    } catch (err) {
        console.error('Error updating data:', err)
    }
}, { deep: true, immediate: false })

// Computed properties for filtered plans
const filteredPlans = computed(() => {
    if (!Array.isArray(plans.value)) return []
    return plans.value.filter(plan => {
        if (!plan) return false
        const matchesSearch = !planFilters.value.search || 
            (plan.plan_name && plan.plan_name.toLowerCase().includes(planFilters.value.search.toLowerCase()))
        
        const matchesStatus = !planFilters.value.status || 
            (planFilters.value.status === 'active' ? plan.is_active : !plan.is_active)
        
        return matchesSearch && matchesStatus
    })
})

// Computed properties for filtered subscriptions
const filteredSubscriptions = computed(() => {
    if (!Array.isArray(clientSubscriptions.value)) return []
    return clientSubscriptions.value.filter(sub => {
        if (!sub) return false
        const matchesSearch = !filters.value.search || 
            (sub.client?.organization_name && sub.client.organization_name.toLowerCase().includes(filters.value.search.toLowerCase())) ||
            (sub.subscription_id && sub.subscription_id.toLowerCase().includes(filters.value.search.toLowerCase()))
        
        const matchesStatus = !filters.value.status || sub.status === filters.value.status
        const matchesPlan = !filters.value.plan_id || sub.plan_id === parseInt(filters.value.plan_id)
        
        return matchesSearch && matchesStatus && matchesPlan
    })
})

// Computed properties for renewal stats
const dueToday = computed(() => {
    if (!Array.isArray(renewals.value)) return 0
    const today = new Date().toDateString()
    return renewals.value.filter(r => r && r.renewal_date && new Date(r.renewal_date).toDateString() === today).length
})

const dueThisWeek = computed(() => {
    if (!Array.isArray(renewals.value)) return 0
    const today = new Date()
    const nextWeek = new Date(today.getTime() + 7 * 24 * 60 * 60 * 1000)
    return renewals.value.filter(r => {
        if (!r || !r.renewal_date) return false
        const date = new Date(r.renewal_date)
        return date >= today && date <= nextWeek
    }).length
})

const dueThisMonth = computed(() => {
    if (!Array.isArray(renewals.value)) return 0
    const today = new Date()
    const nextMonth = new Date(today.getTime() + 30 * 24 * 60 * 60 * 1000)
    return renewals.value.filter(r => {
        if (!r || !r.renewal_date) return false
        const date = new Date(r.renewal_date)
        return date >= today && date <= nextMonth
    }).length
})

const totalRenewalAmount = computed(() => {
    if (!Array.isArray(renewals.value)) return '0.00'
    return renewals.value.reduce((sum, r) => {
        if (!r || !r.plan || !r.plan.price) return sum
        return sum + parseFloat(r.plan.price)
    }, 0).toFixed(2)
})

// Helper functions
const formatDate = (date) => {
    if (!date) return ''
    try {
        return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
    } catch {
        return ''
    }
}

const formatDateForInput = (date) => {
    if (!date) return ''
    try {
        const d = new Date(date)
        return d.toISOString().split('T')[0]
    } catch {
        return ''
    }
}

const daysLeft = (date) => {
    if (!date) return 0
    try {
        const diff = new Date(date) - new Date()
        return Math.ceil(diff / (1000 * 60 * 60 * 24))
    } catch {
        return 0
    }
}

const isExpiringSoon = (date) => {
    const days = daysLeft(date)
    return days <= 7 && days > 0
}

const getPaymentStatusClass = (status) => {
    const map = {
        paid: 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100',
        unpaid: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        partial: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
    }
    return map[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
}

// Renewal helper functions
const getDaysLeftBadgeClass = (date) => {
    const days = daysLeft(date)
    if (days <= 3) return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
    if (days <= 7) return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
    return 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100'
}

const getProgressPercentage = (date) => {
    const days = daysLeft(date)
    if (days <= 0) return 0
    const total = 30
    const progress = ((total - days) / total) * 100
    return Math.min(Math.max(Math.round(progress), 0), 100)
}

const getProgressBarClass = (date) => {
    const days = daysLeft(date)
    if (days <= 3) return 'bg-red-400 dark:bg-red-500'
    if (days <= 7) return 'bg-yellow-400 dark:bg-yellow-500'
    return 'bg-green-400 dark:bg-green-500'
}

// Plan CRUD Operations
const applyPlanFilters = () => {
    // Filters are applied reactively through computed property
}

const editPlan = (plan) => {
    if (!plan) return
    editingPlan.value = plan
    planForm.value = {
        plan_name: plan.plan_name || '',
        billing_cycle: plan.billing_cycle || 'monthly',
        price: plan.price || '',
        trial_days: plan.trial_days || 0,
        support_level: plan.support_level || 'standard',
        max_users: plan.max_users || '',
        max_branches: plan.max_branches || '',
        notes: plan.notes || '',
        is_active: plan.is_active || false
    }
    showCreateModal.value = true
}

const togglePlanStatus = (plan) => {
    if (!plan || !plan.id) return
    loading.value.global = true
    
    router.post(`/subscriptions/plans/${plan.id}/toggle-status`, {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            router.get('/subscriptions', {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                data: { tab: activeTab.value },
                only: ['plans'],
                onSuccess: (page) => {
                    if (page.props.plans) {
                        plans.value = page.props.plans
                    }
                    loading.value.global = false
                }
            })
        },
        onError: (errors) => {
            console.error('Failed to toggle status:', errors)
            loading.value.global = false
        }
    })
}

const confirmDeletePlan = (plan) => {
    if (!plan) return
    selectedPlan.value = plan
    showDeleteModal.value = true
}

const deletePlan = () => {
    if (!selectedPlan.value || !selectedPlan.value.id) return
    
    deleting.value = true
    loading.value.global = true
    router.delete(`/subscriptions/plans/${selectedPlan.value.id}`, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            router.get('/subscriptions', {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                data: { tab: activeTab.value },
                only: ['plans'],
                onSuccess: (page) => {
                    if (page.props.plans) {
                        plans.value = page.props.plans
                    }
                    showDeleteModal.value = false
                    selectedPlan.value = null
                    deleting.value = false
                    loading.value.global = false
                }
            })
        },
        onError: (errors) => {
            console.error('Failed to delete plan:', errors)
            deleting.value = false
            loading.value.global = false
        }
    })
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

const savePlan = () => {
    if (!planForm.value.plan_name || !planForm.value.price) {
        alert('Please fill in all required fields')
        return
    }
    
    saving.value = true
    loading.value.global = true
    
    const url = editingPlan.value && editingPlan.value.id 
        ? `/subscriptions/plans/${editingPlan.value.id}`
        : '/subscriptions/plans'
    
    const method = editingPlan.value && editingPlan.value.id ? 'put' : 'post'
    
    router[method](url, planForm.value, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            closePlanModal()
            
            router.get('/subscriptions', {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                data: { tab: activeTab.value },
                only: ['plans'],
                onSuccess: (page) => {
                    if (page.props.plans) {
                        plans.value = page.props.plans
                    }
                    saving.value = false
                    loading.value.global = false
                }
            })
        },
        onError: (errors) => {
            console.error('Failed to save plan:', errors)
            saving.value = false
            loading.value.global = false
        }
    })
}

// Subscription CRUD Operations
const editSubscription = (subscription) => {
    if (!subscription) return
    selectedSubscription.value = subscription
    editSubscriptionForm.value = {
        id: subscription.id,
        client_id: subscription.client_id || null,
        plan_id: subscription.plan_id || null,
        start_date: formatDateForInput(subscription.start_date),
        end_date: formatDateForInput(subscription.end_date),
        status: subscription.status || 'active',
        payment_method: subscription.payment_method || '',
        payment_status: subscription.payment_status || 'unpaid',
        discount: subscription.discount || 0,
        discount_reason: ''
    }
    showEditSubscriptionModal.value = true
}

const closeEditSubscriptionModal = () => {
    showEditSubscriptionModal.value = false
    selectedSubscription.value = null
    editSubscriptionForm.value = {
        id: null,
        client_id: null,
        plan_id: null,
        start_date: '',
        end_date: '',
        status: 'active',
        payment_method: '',
        payment_status: 'unpaid',
        discount: 0,
        discount_reason: ''
    }
}

const updateSubscription = () => {
    if (!editSubscriptionForm.value.id) return
    
    updating.value = true
    loading.value.global = true
    
    router.put(`/subscriptions/subscription/${editSubscriptionForm.value.id}`, editSubscriptionForm.value, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            router.get('/subscriptions', {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                data: { tab: activeTab.value },
                only: ['clientSubscriptions', 'pendingApprovalsCount'],
                onSuccess: (page) => {
                    if (page.props.clientSubscriptions) {
                        clientSubscriptions.value = page.props.clientSubscriptions
                    }
                    if (page.props.pendingApprovalsCount !== undefined) {
                        pendingApprovalsCount.value = page.props.pendingApprovalsCount
                    }
                    closeEditSubscriptionModal()
                    updating.value = false
                    loading.value.global = false
                }
            })
        },
        onError: (errors) => {
            console.error('Failed to update subscription:', errors)
            updating.value = false
            loading.value.global = false
        }
    })
}

const confirmDeleteSubscription = (subscription) => {
    if (!subscription) return
    selectedSubscription.value = subscription
    showDeleteSubscriptionModal.value = true
}

const deleteSubscription = () => {
    if (!selectedSubscription.value || !selectedSubscription.value.id) return
    
    deletingSubscription.value = true
    loading.value.global = true
    
    router.delete(`/subscriptions/subscription/${selectedSubscription.value.id}`, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            router.get('/subscriptions', {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                data: { tab: activeTab.value },
                only: ['clientSubscriptions', 'pendingApprovalsCount'],
                onSuccess: (page) => {
                    if (page.props.clientSubscriptions) {
                        clientSubscriptions.value = page.props.clientSubscriptions
                    }
                    if (page.props.pendingApprovalsCount !== undefined) {
                        pendingApprovalsCount.value = page.props.pendingApprovalsCount
                    }
                    showDeleteSubscriptionModal.value = false
                    selectedSubscription.value = null
                    deletingSubscription.value = false
                    loading.value.global = false
                }
            })
        },
        onError: (errors) => {
            console.error('Failed to delete subscription:', errors)
            deletingSubscription.value = false
            loading.value.global = false
        }
    })
}

// Fetch renewals with proper days parameter
const fetchRenewals = async () => {
    if (loading.value.renewals) return
    
    loading.value.renewals = true
    renewalError.value = null
    
    try {
        const response = await fetch(`/subscriptions/renewals/due/${renewalDays.value}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`)
        }
        
        const data = await response.json()
        
        if (data.renewals) {
            renewals.value = data.renewals.map(r => ({ ...r, processing: false }))
        } else {
            renewals.value = []
        }
        
        loading.value.renewals = false
    } catch (error) {
        console.error('Error fetching renewals:', error)
        renewalError.value = error.message || 'Failed to fetch renewals'
        loading.value.renewals = false
    }
}

// Process renewal function with fetch
const processRenewal = async (renewal) => {
    if (!renewal || !renewal.id || !renewal.client) return
    if (!confirm(`Process renewal for ${renewal.client?.organization_name || 'this client'}?`)) return
    
    renewal.processing = true
    loading.value.global = true
    
    try {
        const response = await fetch(`/subscriptions/renewals/${renewal.id}/process`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        
        if (!response.ok) {
            const error = await response.json()
            throw new Error(error.error || 'Failed to process renewal')
        }
        
        const data = await response.json()
        
        if (data.success) {
            await fetchRenewals()
            alert('Renewal processed successfully!')
        } else {
            throw new Error(data.error || 'Failed to process renewal')
        }
    } catch (error) {
        console.error('Failed to process renewal:', error)
        alert(error.message || 'Failed to process renewal')
    } finally {
        renewal.processing = false
        loading.value.global = false
    }
}

// Apply subscription filters
const applySubscriptionFilters = () => {
    loading.value.global = true
    router.get('/subscriptions', {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        data: {
            filter_status: filters.value.status,
            filter_plan: filters.value.plan_id,
            search: filters.value.search,
            tab: activeTab.value
        },
        only: ['clientSubscriptions'],
        onSuccess: (page) => {
            if (page.props.clientSubscriptions) {
                clientSubscriptions.value = page.props.clientSubscriptions
            }
            loading.value.global = false
        },
        onError: () => {
            loading.value.global = false
        }
    })
}

// Assign subscription - UPDATED with status field
const assignSubscription = () => {
    const clientId = assignForm.value.client_id
    const planId = assignForm.value.plan_id
    
    if (!clientId || !planId) {
        alert('Please select both client and plan')
        return
    }
    
    assigning.value = true
    loading.value.global = true
    
    const postData = {
        client_id: clientId,
        plan_id: planId,
        start_date: assignForm.value.start_date || null,
        payment_method: assignForm.value.payment_method || null,
        status: assignForm.value.status || 'active',
        discount: assignForm.value.discount || 0,
        discount_reason: assignForm.value.discount_reason || null
    }
    
    router.post('/subscriptions/assign', postData, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            showAssignModal.value = false
            assignForm.value = { 
                client_id: null, 
                plan_id: null, 
                start_date: '', 
                payment_method: '',
                status: 'active',
                discount: 0,
                discount_reason: ''
            }
            
            router.get('/subscriptions', {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                data: { tab: activeTab.value },
                only: ['clientSubscriptions', 'pendingApprovalsCount'],
                onSuccess: (refreshPage) => {
                    if (refreshPage.props.clientSubscriptions) {
                        clientSubscriptions.value = refreshPage.props.clientSubscriptions
                    }
                    if (refreshPage.props.pendingApprovalsCount !== undefined) {
                        pendingApprovalsCount.value = refreshPage.props.pendingApprovalsCount
                    }
                    assigning.value = false
                    loading.value.global = false
                }
            })
        },
        onError: (errors) => {
            console.error('Assignment failed:', errors)
            assigning.value = false
            loading.value.global = false
            alert('Failed to assign subscription')
        }
    })
}

// Watch for tab changes
watch(activeTab, (newTab) => {
    localStorage.setItem('subscriptionsActiveTab', newTab)
    
    if (newTab === 'client-subscriptions' && (!clientSubscriptions.value || clientSubscriptions.value.length === 0)) {
        router.get('/subscriptions', {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            data: { tab: newTab },
            only: ['clientSubscriptions'],
            onSuccess: (page) => {
                if (page.props.clientSubscriptions) {
                    clientSubscriptions.value = page.props.clientSubscriptions
                }
            }
        })
    } else if (newTab === 'renewals' && (!renewals.value || renewals.value.length === 0)) {
        fetchRenewals()
    }
})

// Initialize on mount
onMounted(() => {
    initializeData()
    if (activeTab.value === 'renewals') {
        fetchRenewals()
    }
})
</script>