<template>
    <div class="flex items-center">
        <!-- Pending -->
        <span v-if="status === 'pending'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300 border border-yellow-200 dark:border-yellow-800">
            <ClockIcon class="w-3 h-3 mr-1" />
            Pending
        </span>
        
        <!-- Approved -->
        <span v-else-if="status === 'approved'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 border border-green-200 dark:border-green-800">
            <CheckCircleIcon class="w-3 h-3 mr-1" />
            Approved
        </span>
        
        <!-- Rejected -->
        <span v-else-if="status === 'rejected'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 border border-red-200 dark:border-red-800">
            <XCircleIcon class="w-3 h-3 mr-1" />
            Rejected
        </span>
        
        <!-- Unknown -->
        <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
            <QuestionMarkCircleIcon class="w-3 h-3 mr-1" />
            {{ status || 'Unknown' }}
        </span>
    </div>
</template>

<script setup>
import { ClockIcon, CheckCircleIcon, XCircleIcon, QuestionMarkCircleIcon } from '@heroicons/vue/24/outline'

defineProps({
    status: {
        type: String,
        default: 'pending'
    }
})
</script>