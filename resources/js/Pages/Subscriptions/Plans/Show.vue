<template>
    <AuthenticatedLayout>
        <template #breadcrumbs>
            <div class="flex items-center space-x-2 text-sm">
                <Link href="/dashboard" class="text-gray-500 hover:text-teal-600 dark:text-gray-400 dark:hover:text-teal-400 transition-colors">Dashboard</Link>
                <ChevronRightIcon class="w-4 h-4 text-gray-400 dark:text-gray-500" />
                <Link href="/subscriptions" class="text-gray-500 hover:text-teal-600 dark:text-gray-400 dark:hover:text-teal-400">Subscriptions</Link>
                <ChevronRightIcon class="w-4 h-4 text-gray-400 dark:text-gray-500" />
                <span class="text-gray-900 dark:text-white font-medium">{{ plan.plan_name }}</span>
            </div>
        </template>

        <div class="space-y-8">
            <!-- Header with decorative gradient -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-teal-600 to-green-600 dark:from-teal-800 dark:to-green-800 p-8 shadow-xl">
                <!-- Decorative pattern -->
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-32 translate-x-32"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-black/10 rounded-full -translate-x-24 translate-y-24"></div>
                
                <div class="relative">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center space-x-3 mb-2">
                                <span :class="plan.is_active ? 'bg-green-200 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-200 text-red-800 dark:bg-red-900 dark:text-red-200'"
                                      class="px-3 py-1 text-xs font-semibold rounded-full backdrop-blur-sm">
                                    {{ plan.is_active ? 'ACTIVE' : 'INACTIVE' }}
                                </span>
                                <span class="text-white/80 text-sm flex items-center">
                                    <CalendarIcon class="w-4 h-4 mr-1" />
                                    Created {{ formatDate(plan.created_at) }}
                                </span>
                            </div>
                            <h1 class="text-4xl font-bold text-white mb-2">{{ plan.plan_name }}</h1>
                            <p class="text-teal-100 text-lg flex items-center">
                                <TagIcon class="w-5 h-5 mr-2" />
                                ${{ plan.price }} / {{ plan.billing_cycle }}
                            </p>
                        </div>
                        
                        <!-- Quick stats pill -->
                        <div class="bg-white/20 backdrop-blur-sm rounded-2xl px-6 py-4 text-white">
                            <p class="text-sm text-teal-100">Active Subscribers</p>
                            <p class="text-3xl font-bold">{{ plan.active_subscriptions_count || 0 }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Plan Details Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column - Plan Information (2/3 width) -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Plan Information Card - Dark Green in dark mode -->
                    <div class="group bg-white dark:bg-green-950 rounded-2xl shadow-lg border border-gray-100 dark:border-green-800 overflow-hidden hover:shadow-xl transition-all duration-300">
                        <div class="px-8 py-5 border-b border-gray-100 dark:border-green-800 bg-gray-50 dark:bg-green-900">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-green-100 flex items-center">
                                <InformationCircleIcon class="w-5 h-5 mr-2 text-teal-600 dark:text-teal-400" />
                                Plan Information
                            </h3>
                        </div>
                        <div class="p-8">
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-1">
                                    <dt class="text-sm text-gray-500 dark:text-green-300">Plan Name</dt>
                                    <dd class="text-xl font-semibold text-gray-900 dark:text-green-100">{{ plan.plan_name }}</dd>
                                </div>
                                <div class="space-y-1">
                                    <dt class="text-sm text-gray-500 dark:text-green-300">Billing Cycle</dt>
                                    <dd class="text-xl font-semibold text-gray-900 dark:text-green-100 capitalize">{{ plan.billing_cycle }}</dd>
                                </div>
                                <div class="space-y-1">
                                    <dt class="text-sm text-gray-500 dark:text-green-300">Price</dt>
                                    <dd class="text-xl font-semibold text-gray-900 dark:text-green-100">${{ plan.price }}</dd>
                                </div>
                                <div class="space-y-1">
                                    <dt class="text-sm text-gray-500 dark:text-green-300">Trial Days</dt>
                                    <dd class="text-xl font-semibold text-gray-900 dark:text-green-100">{{ plan.trial_days }} days</dd>
                                </div>
                                <div class="space-y-1">
                                    <dt class="text-sm text-gray-500 dark:text-green-300">Support Level</dt>
                                    <dd class="text-xl font-semibold text-gray-900 dark:text-green-100 capitalize">{{ plan.support_level || 'Standard' }}</dd>
                                </div>
                                <div class="space-y-1">
                                    <dt class="text-sm text-gray-500 dark:text-green-300">Active Subscriptions</dt>
                                    <dd class="text-xl font-semibold text-teal-600 dark:text-teal-400">{{ plan.active_subscriptions_count || 0 }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Limits Card - Dark Green in dark mode -->
                    <div class="group bg-white dark:bg-green-950 rounded-2xl shadow-lg border border-gray-100 dark:border-green-800 overflow-hidden hover:shadow-xl transition-all duration-300">
                        <div class="px-8 py-5 border-b border-gray-100 dark:border-green-800 bg-gray-50 dark:bg-green-900">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-green-100 flex items-center">
                                <Cog6ToothIcon class="w-5 h-5 mr-2 text-teal-600 dark:text-teal-400" />
                                Plan Limits
                            </h3>
                        </div>
                        <div class="p-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-1">
                                    <dt class="text-sm text-gray-500 dark:text-green-300">Max Users</dt>
                                    <dd class="text-xl font-semibold text-gray-900 dark:text-green-100">
                                        <span class="inline-flex items-center">
                                            <UsersIcon class="w-5 h-5 mr-2 text-teal-600 dark:text-teal-400" />
                                            {{ plan.max_users || 'Unlimited' }}
                                        </span>
                                    </dd>
                                </div>
                                <div class="space-y-1">
                                    <dt class="text-sm text-gray-500 dark:text-green-300">Max Branches</dt>
                                    <dd class="text-xl font-semibold text-gray-900 dark:text-green-100">
                                        <span class="inline-flex items-center">
                                            <BuildingOfficeIcon class="w-5 h-5 mr-2 text-teal-600 dark:text-teal-400" />
                                            {{ plan.max_branches || 'Unlimited' }}
                                        </span>
                                    </dd>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modules Included - Dark Green in dark mode -->
                    <div v-if="modules.length" class="group bg-white dark:bg-green-950 rounded-2xl shadow-lg border border-gray-100 dark:border-green-800 overflow-hidden hover:shadow-xl transition-all duration-300">
                        <div class="px-8 py-5 border-b border-gray-100 dark:border-green-800 bg-gray-50 dark:bg-green-900">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-green-100 flex items-center">
                                <CubeIcon class="w-5 h-5 mr-2 text-teal-600 dark:text-teal-400" />
                                Modules Included
                            </h3>
                        </div>
                        <div class="p-8">
                            <div class="flex flex-wrap gap-3">
                                <span 
                                    v-for="module in modules" 
                                    :key="module"
                                    class="px-4 py-2 bg-teal-50 text-teal-700 dark:bg-green-800 dark:text-teal-200 rounded-xl text-sm font-medium border border-teal-200 dark:border-green-700 shadow-sm"
                                >
                                    {{ module }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Notes Card - Dark Green in dark mode -->
                    <div v-if="plan.notes" class="group bg-white dark:bg-green-950 rounded-2xl shadow-lg border border-gray-100 dark:border-green-800 overflow-hidden hover:shadow-xl transition-all duration-300">
                        <div class="px-8 py-5 border-b border-gray-100 dark:border-green-800 bg-gray-50 dark:bg-green-900">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-green-100 flex items-center">
                                <DocumentTextIcon class="w-5 h-5 mr-2 text-teal-600 dark:text-teal-400" />
                                Notes
                            </h3>
                        </div>
                        <div class="p-8">
                            <p class="text-gray-700 dark:text-green-200 leading-relaxed">{{ plan.notes }}</p>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Revenue Graph and Timeline (1/3 width) -->
                <div class="space-y-8">
                    <!-- Revenue Line Graph - Dark Green in dark mode -->
                    <div class="group bg-white dark:bg-green-950 rounded-2xl shadow-lg border border-gray-100 dark:border-green-800 overflow-hidden hover:shadow-xl transition-all duration-300">
                        <div class="px-8 py-5 border-b border-gray-100 dark:border-green-800 bg-gray-50 dark:bg-green-900">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-green-100 flex items-center">
                                <ChartBarIcon class="w-5 h-5 mr-2 text-teal-600 dark:text-teal-400" />
                                Revenue Trend
                            </h3>
                        </div>
                        <div class="p-6">
                            <!-- Chart.js Line Graph -->
                            <div class="h-64">
                                <canvas ref="revenueChart"></canvas>
                            </div>
                            
                            <!-- Monthly Stats Summary -->
                            <div class="grid grid-cols-3 gap-4 mt-6 pt-6 border-t border-gray-100 dark:border-green-800">
                                <div class="text-center p-3 bg-teal-50 dark:bg-green-900 rounded-xl">
                                    <p class="text-xs text-teal-600 dark:text-teal-400 mb-1">This Month</p>
                                    <p class="text-lg font-bold text-gray-900 dark:text-green-100">${{ currentMonthRevenue }}</p>
                                </div>
                                <div class="text-center p-3 bg-teal-50 dark:bg-green-900 rounded-xl">
                                    <p class="text-xs text-teal-600 dark:text-teal-400 mb-1">Last Month</p>
                                    <p class="text-lg font-bold text-gray-900 dark:text-green-100">${{ lastMonthRevenue }}</p>
                                </div>
                                <div class="text-center p-3 bg-teal-50 dark:bg-green-900 rounded-xl">
                                    <p class="text-xs text-teal-600 dark:text-teal-400 mb-1">Growth</p>
                                    <p class="text-lg font-bold" :class="revenueGrowth >= 0 ? 'text-green-600' : 'text-red-600'">
                                        {{ revenueGrowth >= 0 ? '+' : '' }}{{ revenueGrowth }}%
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Plan Timeline - Dark Green in dark mode -->
                    <div class="group bg-white dark:bg-green-950 rounded-2xl shadow-lg border border-gray-100 dark:border-green-800 overflow-hidden hover:shadow-xl transition-all duration-300">
                        <div class="px-8 py-5 border-b border-gray-100 dark:border-green-800 bg-gray-50 dark:bg-green-900">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-green-100 flex items-center">
                                <ClockIcon class="w-5 h-5 mr-2 text-teal-600 dark:text-teal-400" />
                                Plan Timeline
                            </h3>
                        </div>
                        <div class="p-6 max-h-96 overflow-y-auto custom-scrollbar">
                            <div class="relative">
                                <!-- Timeline line -->
                                <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gradient-to-b from-teal-500 to-green-500 dark:from-teal-400 dark:to-green-400"></div>
                                
                                <!-- Timeline Items -->
                                <div v-for="(event, index) in timeline" :key="index" class="relative pl-12 pb-8 group/timeline">
                                    <!-- Timeline dot with pulse effect -->
                                    <div class="absolute left-2 w-5 h-5 rounded-full" :class="getTimelineDotColor(event.type)">
                                        <div class="absolute inset-0 rounded-full animate-ping opacity-20" :class="getTimelineDotColor(event.type)"></div>
                                    </div>
                                    
                                    <!-- Content -->
                                    <div class="bg-gray-50 dark:bg-green-900 p-5 rounded-xl shadow-sm hover:shadow-md transition-all duration-300">
                                        <div class="flex items-start justify-between mb-2">
                                            <h4 class="text-sm font-bold text-gray-900 dark:text-green-100">{{ event.title }}</h4>
                                            <span class="text-xs px-2 py-1 bg-teal-100 dark:bg-green-800 text-teal-700 dark:text-teal-300 rounded-full">{{ event.time }}</span>
                                        </div>
                                        <p class="text-xs text-gray-600 dark:text-green-300">{{ event.description }}</p>
                                        
                                        <!-- Additional details based on event type -->
                                        <div v-if="event.details" class="mt-3 text-xs border-t border-gray-200 dark:border-green-700 pt-3">
                                            <span v-if="event.type === 'subscription_added'" class="text-teal-600 dark:text-teal-400 font-medium">
                                                <UserIcon class="w-3 h-3 inline mr-1" />
                                                {{ event.details.client }} - ${{ event.details.amount }}
                                            </span>
                                            <span v-else-if="event.type === 'status_changed'" class="text-yellow-600 dark:text-yellow-400 font-medium">
                                                <ArrowPathIcon class="w-3 h-3 inline mr-1" />
                                                {{ event.details.old }} → {{ event.details.new }}
                                            </span>
                                            <span v-else-if="event.type === 'updated'" class="text-blue-600 dark:text-blue-400 font-medium">
                                                <PencilIcon class="w-3 h-3 inline mr-1" />
                                                {{ event.details.fields }} updated
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Empty State -->
                                <div v-if="timeline.length === 0" class="relative pl-12">
                                    <div class="absolute left-2 w-5 h-5 rounded-full bg-gray-300 dark:bg-gray-700"></div>
                                    <div class="bg-gray-50 dark:bg-green-900 p-8 rounded-xl text-center">
                                        <ClockIcon class="w-12 h-12 mx-auto text-gray-400 dark:text-green-600 mb-3" />
                                        <p class="text-sm text-gray-500 dark:text-green-300">No timeline events yet</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscribers List - Dark Green in dark mode -->
            <div class="group bg-white dark:bg-green-950 rounded-2xl shadow-lg border border-gray-100 dark:border-green-800 overflow-hidden hover:shadow-xl transition-all duration-300">
                <div class="px-8 py-5 border-b border-gray-100 dark:border-green-800 bg-gray-50 dark:bg-green-900 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-green-100 flex items-center">
                        <UsersIcon class="w-5 h-5 mr-2 text-teal-600 dark:text-teal-400" />
                        Current Subscribers
                    </h3>
                    <span class="px-3 py-1.5 bg-teal-100 dark:bg-green-800 text-teal-700 dark:text-teal-300 rounded-xl text-xs font-medium shadow-sm">
                        {{ plan.active_subscriptions_count || 0 }} Active
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-green-800">
                        <thead class="bg-gray-100 dark:bg-green-900">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-green-300 uppercase tracking-wider">Client</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-green-300 uppercase tracking-wider">Subscription ID</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-green-300 uppercase tracking-wider">Start Date</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-green-300 uppercase tracking-wider">End Date</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-green-300 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-green-300 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-green-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-green-800">
                            <tr v-for="sub in plan.subscriptions" :key="sub.id" class="hover:bg-gray-50 dark:hover:bg-green-900 transition-colors duration-150">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-teal-500 to-green-500 flex items-center justify-center text-white text-sm font-bold shadow-md">
                                            {{ sub.client?.organization_name?.charAt(0) }}
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-semibold text-gray-900 dark:text-green-100">{{ sub.client?.organization_name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-green-300">{{ sub.client?.primary_contact_email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-mono font-medium text-gray-900 dark:text-green-100">{{ sub.subscription_id }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-green-300">{{ formatDate(sub.start_date) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-green-300">{{ formatDate(sub.end_date) }}</td>
                                <td class="px-6 py-4">
                                    <SubscriptionStatusBadge :status="sub.status" />
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-900 dark:text-green-100">${{ sub.amount || plan.price }}</td>
                                <td class="px-6 py-4">
                                    <Link :href="`/subscriptions/subscription/${sub.id}`" 
                                          class="inline-flex items-center px-3 py-1.5 bg-teal-600 hover:bg-teal-700 dark:bg-teal-700 dark:hover:bg-teal-800 text-white rounded-xl text-xs font-medium transition-all duration-200 shadow-sm hover:shadow-md">
                                        View Details
                                        <ArrowRightIcon class="w-3 h-3 ml-1" />
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="!plan.subscriptions?.length">
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <UsersIcon class="w-12 h-12 text-gray-400 dark:text-green-600 mb-3" />
                                        <p class="text-sm text-gray-500 dark:text-green-300">No subscribers yet for this plan</p>
                                    </div>
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
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import SubscriptionStatusBadge from '@/Components/Subscription/SubscriptionStatusBadge.vue'
import {
    ChevronRightIcon,
    CalendarIcon,
    TagIcon,
    InformationCircleIcon,
    Cog6ToothIcon,
    UsersIcon,
    BuildingOfficeIcon,
    CubeIcon,
    DocumentTextIcon,
    ChartBarIcon,
    ClockIcon,
    ArrowPathIcon,
    PencilIcon,
    UserIcon,
    ArrowRightIcon
} from '@heroicons/vue/24/outline'
import Chart from 'chart.js/auto'

const props = defineProps({
    plan: {
        type: Object,
        required: true
    },
    can: {
        type: Object,
        default: () => ({})
    }
})

// Chart reference
const revenueChart = ref(null)
let chartInstance = null

// Modules computed
const modules = computed(() => {
    if (!props.plan?.modules_included) return []
    if (Array.isArray(props.plan.modules_included)) return props.plan.modules_included
    try {
        return JSON.parse(props.plan.modules_included || '[]')
    } catch {
        return []
    }
})

// Revenue calculations for the graph
const currentMonthRevenue = computed(() => {
    const now = new Date()
    const currentMonth = now.getMonth()
    const currentYear = now.getFullYear()
    
    return props.plan.subscriptions
        ?.filter(sub => {
            const date = new Date(sub.created_at)
            return date.getMonth() === currentMonth && date.getFullYear() === currentYear
        })
        .reduce((sum, sub) => sum + parseFloat(sub.amount || props.plan.price), 0)
        .toFixed(2) || '0.00'
})

const lastMonthRevenue = computed(() => {
    const now = new Date()
    const lastMonth = now.getMonth() - 1
    const year = lastMonth < 0 ? now.getFullYear() - 1 : now.getFullYear()
    const month = lastMonth < 0 ? 11 : lastMonth
    
    return props.plan.subscriptions
        ?.filter(sub => {
            const date = new Date(sub.created_at)
            return date.getMonth() === month && date.getFullYear() === year
        })
        .reduce((sum, sub) => sum + parseFloat(sub.amount || props.plan.price), 0)
        .toFixed(2) || '0.00'
})

const revenueGrowth = computed(() => {
    const current = parseFloat(currentMonthRevenue.value)
    const last = parseFloat(lastMonthRevenue.value)
    
    if (last === 0) return current > 0 ? 100 : 0
    return ((current - last) / last * 100).toFixed(1)
})

// Timeline data
const timeline = computed(() => {
    const events = []
    
    // Plan creation
    if (props.plan.created_at) {
        events.push({
            type: 'created',
            title: 'Plan Created',
            description: `Plan "${props.plan.plan_name}" was created`,
            time: formatTimeAgo(props.plan.created_at),
            date: new Date(props.plan.created_at)
        })
    }
    
    // Plan updates (from history if available)
    if (props.plan.history) {
        props.plan.history.forEach(item => {
            if (item.action === 'updated') {
                events.push({
                    type: 'updated',
                    title: 'Plan Updated',
                    description: 'Plan details were modified',
                    time: formatTimeAgo(item.created_at),
                    date: new Date(item.created_at),
                    details: {
                        fields: item.changes || 'Information'
                    }
                })
            } else if (item.action === 'status_changed') {
                events.push({
                    type: 'status_changed',
                    title: 'Status Changed',
                    description: `Plan status changed to ${item.new_status}`,
                    time: formatTimeAgo(item.created_at),
                    date: new Date(item.created_at),
                    details: {
                        old: item.old_status,
                        new: item.new_status
                    }
                })
            }
        })
    }
    
    // Subscription assignments
    if (props.plan.subscriptions) {
        props.plan.subscriptions.forEach(sub => {
            events.push({
                type: 'subscription_added',
                title: 'New Subscriber',
                description: `${sub.client?.organization_name} subscribed to this plan`,
                time: formatTimeAgo(sub.created_at),
                date: new Date(sub.created_at),
                details: {
                    client: sub.client?.organization_name,
                    amount: sub.amount || props.plan.price
                }
            })
        })
    }
    
    // Sort by date (newest first)
    return events.sort((a, b) => b.date - a.date)
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

const formatTimeAgo = (date) => {
    const now = new Date()
    const past = new Date(date)
    const diffMs = now - past
    const diffSec = Math.floor(diffMs / 1000)
    const diffMin = Math.floor(diffSec / 60)
    const diffHour = Math.floor(diffMin / 60)
    const diffDay = Math.floor(diffHour / 24)
    
    if (diffDay > 30) {
        return formatDate(date)
    } else if (diffDay > 0) {
        return `${diffDay}d ago`
    } else if (diffHour > 0) {
        return `${diffHour}h ago`
    } else if (diffMin > 0) {
        return `${diffMin}m ago`
    } else {
        return 'Just now'
    }
}

const getTimelineDotColor = (type) => {
    const colors = {
        created: 'bg-green-500',
        updated: 'bg-blue-500',
        status_changed: 'bg-yellow-500',
        subscription_added: 'bg-teal-500'
    }
    return colors[type] || 'bg-gray-500'
}

// Initialize chart
onMounted(() => {
    if (revenueChart.value) {
        // Generate last 6 months of revenue data
        const months = []
        const revenueData = []
        
        for (let i = 5; i >= 0; i--) {
            const date = new Date()
            date.setMonth(date.getMonth() - i)
            months.push(date.toLocaleDateString('en-US', { month: 'short' }))
            
            // Calculate revenue for this month
            const monthRevenue = props.plan.subscriptions
                ?.filter(sub => {
                    const subDate = new Date(sub.created_at)
                    return subDate.getMonth() === date.getMonth() && 
                           subDate.getFullYear() === date.getFullYear()
                })
                .reduce((sum, sub) => sum + parseFloat(sub.amount || props.plan.price), 0) || 0
            
            revenueData.push(monthRevenue)
        }
        
        // Create chart
        chartInstance = new Chart(revenueChart.value, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Revenue ($)',
                    data: revenueData,
                    borderColor: '#14b8a6',
                    backgroundColor: 'rgba(20, 184, 166, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#14b8a6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: (context) => `$${context.raw.toFixed(2)}`
                        },
                        backgroundColor: '#1f2937',
                        titleColor: '#f3f4f6',
                        bodyColor: '#d1d5db'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(156, 163, 175, 0.1)'
                        },
                        ticks: {
                            callback: (value) => `$${value}`,
                            color: '#6b7280'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#6b7280'
                        }
                    }
                }
            }
        })
    }
})

// Cleanup chart on component unmount
onUnmounted(() => {
    if (chartInstance) {
        chartInstance.destroy()
    }
})
</script>

<style scoped>
/* Custom scrollbar for timeline */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #14b8a6;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #0d9488;
}

.dark .custom-scrollbar::-webkit-scrollbar-track {
    background: #1f2937;
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background: #059669;
}

.dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #047857;
}

/* Animation for timeline dots */
@keyframes pulse {
    0%, 100% {
        opacity: 0.2;
    }
    50% {
        opacity: 0.5;
    }
}

.animate-ping {
    animation: ping 1.5s cubic-bezier(0.4, 0, 0.2, 1) infinite;
}

@keyframes ping {
    75%, 100% {
        transform: scale(1.5);
        opacity: 0;
    }
}
</style>