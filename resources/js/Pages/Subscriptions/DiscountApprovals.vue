<template>
    <AuthenticatedLayout>
        <template #breadcrumbs>
            <div class="flex items-center space-x-2 text-sm">
                <Link href="/dashboard" class="text-gray-500 hover:text-teal-600 dark:text-gray-400 dark:hover:text-teal-400 transition-colors">Dashboard</Link>
                <ChevronRightIcon class="w-4 h-4 text-gray-400 dark:text-gray-500" />
                <Link href="/subscriptions" class="text-gray-500 hover:text-teal-600 dark:text-gray-400 dark:hover:text-teal-400 transition-colors">Subscriptions</Link>
                <ChevronRightIcon class="w-4 h-4 text-gray-400 dark:text-gray-500" />
                <span class="text-gray-900 dark:text-white font-medium">Discount Approvals</span>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header Section -->
                <div class="mb-8">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <span class="bg-teal-100 dark:bg-teal-900/30 p-2 rounded-lg">
                                    <TagIcon class="w-5 h-5 text-teal-600 dark:text-teal-400" />
                                </span>
                                Discount Approval Requests
                            </h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 ml-11">
                                Review and manage discount requests from client subscriptions
                            </p>
                        </div>
                        
                        <!-- Filter and Refresh -->
                        <div class="flex items-center gap-2">
                            <div class="relative">
                                <select 
                                    v-model="statusFilter"
                                    @change="filterApprovals"
                                    class="pl-3 pr-8 py-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-sm text-gray-700 dark:text-gray-300 appearance-none cursor-pointer"
                                >
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="all">All</option>
                                </select>
                                <ChevronDownIcon class="absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" />
                            </div>
                            
                            <button 
                                @click="refreshApprovals"
                                class="p-2 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-400 transition-colors"
                                :class="{ 'animate-spin': loading }"
                                title="Refresh"
                            >
                                <ArrowPathIcon class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Success Message -->
                <div v-if="successMessage" class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <CheckCircleIcon class="w-5 h-5 text-green-600 dark:text-green-400" />
                            <p class="text-green-600 dark:text-green-400">{{ successMessage }}</p>
                        </div>
                        <button @click="successMessage = null" class="text-green-600 dark:text-green-400 hover:text-green-800">
                            <XMarkIcon class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                <!-- Error Display -->
                <div v-if="error" class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <ExclamationCircleIcon class="w-5 h-5 text-red-600 dark:text-red-400" />
                            <p class="text-red-600 dark:text-red-400">{{ error }}</p>
                        </div>
                        <button @click="error = null" class="text-red-600 dark:text-red-400 hover:text-red-800">
                            <XMarkIcon class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="flex justify-center items-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-teal-600"></div>
                </div>

                <!-- Empty State -->
                <div v-else-if="!filteredApprovals.length" class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-12 text-center">
                    <div class="w-16 h-16 mx-auto bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4">
                        <CheckCircleIcon class="w-8 h-8 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No requests found</h3>
                    <p class="text-gray-500 dark:text-gray-400">
                        {{ statusFilter === 'pending' ? 'All discount requests have been reviewed.' : 'No requests match the selected filter.' }}
                    </p>
                </div>

                <!-- Cards Grid - Responsive -->
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="approval in filteredApprovals" :key="approval.id" 
                         class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm hover:shadow-md transition-all duration-200 relative">
                        
                        <!-- Status Bar -->
                        <div class="h-1.5 w-full" :class="{
                            'bg-yellow-500': approval.status === 'pending',
                            'bg-green-500': approval.status === 'approved',
                            'bg-red-500': approval.status === 'rejected'
                        }"></div>
                        
                        <div class="p-5">
                            <!-- Header with Client and Status -->
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-teal-500 to-green-500 flex items-center justify-center text-white font-semibold text-lg flex-shrink-0">
                                        {{ approval.subscription?.client?.organization_name?.charAt(0) || '?' }}
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="font-semibold text-gray-900 dark:text-white truncate">
                                            {{ approval.subscription?.client?.organization_name || 'Unknown Client' }}
                                        </h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                            {{ approval.subscription?.subscription_id || 'No ID' }}
                                        </p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full whitespace-nowrap flex-shrink-0" :class="{
                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300': approval.status === 'pending',
                                    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300': approval.status === 'approved',
                                    'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300': approval.status === 'rejected'
                                }">
                                    {{ approval.status }}
                                </span>
                            </div>
                            
                            <!-- Plan Info -->
                            <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Plan</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white truncate max-w-[150px]">{{ approval.subscription?.plan?.plan_name || 'N/A' }}</span>
                                </div>
                                <div class="grid grid-cols-3 gap-2 text-center">
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Original</p>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">${{ approval.original_price || 0 }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Discount</p>
                                        <p class="text-sm font-semibold text-teal-600 dark:text-teal-400">
                                            ${{ approval.discount_amount || 0 }}
                                            <span v-if="approval.discount_percentage" class="text-xs">({{ Math.round(approval.discount_percentage) }}%)</span>
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Final</p>
                                        <p class="text-sm font-semibold text-green-600 dark:text-green-400">
                                            ${{ (approval.original_price - approval.discount_amount).toFixed(2) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Reason -->
                            <div v-if="approval.reason" class="mb-4">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Reason</p>
                                <p class="text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800/50 p-3 rounded-lg break-words">
                                    {{ approval.reason }}
                                </p>
                            </div>
                            
                            <!-- Rejection Reason -->
                            <div v-if="approval.status === 'rejected' && approval.rejection_reason" class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                                <p class="text-xs text-red-600 dark:text-red-400 mb-1">Rejection reason</p>
                                <p class="text-sm text-red-700 dark:text-red-300 break-words">{{ approval.rejection_reason }}</p>
                            </div>
                            
                            <!-- Meta Info -->
                            <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500 dark:text-gray-400 mb-4 pb-3 border-b border-gray-200 dark:border-gray-700">
                                <span class="truncate max-w-[150px]">Requested: {{ approval.requested_by?.name || 'System' }}</span>
                                <span class="whitespace-nowrap">{{ formatDate(approval.created_at) }}</span>
                            </div>
                            
                            <!-- Actions -->
                            <div class="flex items-center justify-between">
                                <Link 
                                    :href="`/subscriptions/subscription/${approval.subscription_id}`"
                                    class="text-sm text-teal-600 hover:text-teal-700 dark:text-teal-400 font-medium inline-flex items-center gap-1"
                                >
                                    View Details
                                    <ChevronRightIcon class="w-4 h-4" />
                                </Link>
                                
                                <div v-if="approval.status === 'pending'" class="flex items-center gap-2">
                                    <button 
                                        @click="approveDiscount(approval)"
                                        :disabled="processingId === approval.id"
                                        class="p-2 bg-green-100 hover:bg-green-200 text-green-700 dark:bg-green-900/30 dark:hover:bg-green-900/50 dark:text-green-400 rounded-lg transition-colors disabled:opacity-50"
                                        title="Approve"
                                    >
                                        <CheckIcon class="w-5 h-5" />
                                    </button>
                                    
                                    <button 
                                        @click="openRejectModal(approval)"
                                        :disabled="processingId === approval.id"
                                        class="p-2 bg-red-100 hover:bg-red-200 text-red-700 dark:bg-red-900/30 dark:hover:bg-red-900/50 dark:text-red-400 rounded-lg transition-colors disabled:opacity-50"
                                        title="Reject"
                                    >
                                        <XMarkIcon class="w-5 h-5" />
                                    </button>
                                </div>
                                
                                <div v-else-if="approval.status === 'approved'" class="flex items-center gap-1 text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 px-3 py-1.5 rounded-lg">
                                    <CheckCircleIcon class="w-4 h-4" />
                                    <span class="text-sm">Approved</span>
                                </div>
                                
                                <div v-else-if="approval.status === 'rejected'" class="flex items-center gap-1 text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 px-3 py-1.5 rounded-lg">
                                    <XCircleIcon class="w-4 h-4" />
                                    <span class="text-sm">Rejected</span>
                                </div>
                            </div>
                            
                            <!-- Processing Overlay -->
                            <div v-if="processingId === approval.id" class="absolute inset-0 bg-white/80 dark:bg-gray-900/80 flex items-center justify-center rounded-xl">
                                <div class="flex items-center gap-2 bg-white dark:bg-gray-800 px-4 py-2 rounded-lg shadow-lg">
                                    <div class="w-4 h-4 border-2 border-teal-600 border-t-transparent rounded-full animate-spin"></div>
                                    <span class="text-sm font-medium">Processing...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <Modal :show="showRejectModal" @close="showRejectModal = false" maxWidth="md">
            <div class="p-6 bg-white dark:bg-gray-900">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Reject Discount Request</h3>
                
                <div class="mb-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="flex flex-col gap-2">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Client:</span>
                            <span class="font-medium text-gray-900 dark:text-white truncate max-w-[200px]">{{ selectedApproval?.subscription?.client?.organization_name }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Discount:</span>
                            <span class="font-medium text-red-600 dark:text-red-400">${{ selectedApproval?.discount_amount || 0 }}</span>
                        </div>
                    </div>
                </div>
                
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Rejection Reason <span class="text-red-500">*</span>
                </label>
                <textarea 
                    v-model="rejectionReason" 
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 dark:bg-gray-800 dark:text-white"
                    placeholder="Enter reason for rejection..."
                ></textarea>
                
                <div class="mt-6 flex justify-end gap-3">
                    <button 
                        @click="showRejectModal = false" 
                        class="px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 font-medium transition-colors"
                    >
                        Cancel
                    </button>
                    <button 
                        @click="rejectDiscount" 
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="rejecting || !rejectionReason.trim()"
                    >
                        {{ rejecting ? 'Rejecting...' : 'Reject Request' }}
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'
import {
    ChevronRightIcon,
    ChevronDownIcon,
    ArrowPathIcon,
    EyeIcon,
    ExclamationCircleIcon,
    XMarkIcon,
    CheckCircleIcon,
    XCircleIcon,
    CheckIcon,
    TagIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    approvals: {
        type: Array,
        default: () => []
    },
    error: {
        type: String,
        default: null
    }
})

// State
const loading = ref(false)
const processingId = ref(null)
const showRejectModal = ref(false)
const rejectionReason = ref('')
const selectedApproval = ref(null)
const rejecting = ref(false)
const statusFilter = ref('pending')
const error = ref(props.error)
const successMessage = ref(null)

// Computed
const filteredApprovals = computed(() => {
    if (!Array.isArray(props.approvals)) return []
    
    if (statusFilter.value === 'all') {
        return props.approvals
    }
    
    return props.approvals.filter(a => a.status === statusFilter.value)
})

// Methods
const formatDate = (date) => {
    if (!date) return ''
    try {
        return new Date(date).toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric',
            year: 'numeric'
        })
    } catch {
        return ''
    }
}

const refreshApprovals = () => {
    loading.value = true
    error.value = null
    
    router.visit('/subscriptions/discount-approvals/pending', {
        preserveState: true,
        preserveScroll: true,
        only: ['approvals'],
        onSuccess: (page) => {
            loading.value = false
        },
        onError: (errors) => {
            loading.value = false
            error.value = 'Failed to refresh approvals'
        }
    })
}

const filterApprovals = () => {
    // Filter is applied via computed
}

const approveDiscount = (approval) => {
    if (!confirm(`Are you sure you want to approve $${approval.discount_amount} discount for ${approval.subscription?.client?.organization_name}?`)) return
    
    processingId.value = approval.id
    error.value = null
    successMessage.value = null
    
    router.post(`/subscriptions/discount-approvals/${approval.id}/approve`, {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            processingId.value = null
            successMessage.value = 'Discount approved successfully!'
            
            setTimeout(() => {
                router.visit('/subscriptions', {
                    data: { 
                        success: 'Discount approved successfully!',
                        tab: 'client-subscriptions'
                    }
                })
            }, 1500)
        },
        onError: (errors) => {
            processingId.value = null
            if (errors.response?.data?.message) {
                error.value = errors.response.data.message
            } else if (errors.message) {
                error.value = errors.message
            } else {
                error.value = 'Failed to approve discount.'
            }
        }
    })
}

const openRejectModal = (approval) => {
    selectedApproval.value = approval
    rejectionReason.value = ''
    showRejectModal.value = true
}

const rejectDiscount = () => {
    if (!rejectionReason.value.trim()) {
        alert('Please provide a rejection reason')
        return
    }
    
    rejecting.value = true
    processingId.value = selectedApproval.value.id
    error.value = null
    successMessage.value = null
    
    router.post(`/subscriptions/discount-approvals/${selectedApproval.value.id}/reject`, {
        reason: rejectionReason.value
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            showRejectModal.value = false
            rejectionReason.value = ''
            selectedApproval.value = null
            rejecting.value = false
            processingId.value = null
            successMessage.value = 'Discount rejected successfully!'
            
            setTimeout(() => {
                router.visit('/subscriptions', {
                    data: { 
                        success: 'Discount rejected successfully!',
                        tab: 'client-subscriptions'
                    }
                })
            }, 1500)
        },
        onError: (errors) => {
            rejecting.value = false
            processingId.value = null
            if (errors.response?.data?.message) {
                error.value = errors.response.data.message
            } else if (errors.message) {
                error.value = errors.message
            } else {
                error.value = 'Failed to reject discount.'
            }
        }
    })
}
</script>