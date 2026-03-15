<template>
    <span :class="badgeClasses" class="px-3 py-1 text-xs font-medium rounded-full">
        {{ statusLabel }}
    </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    status: {
        type: String,
        required: true
    }
})

const statusMap = {
    trial: { label: 'Trial', class: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100' },
    active: { label: 'Active', class: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100' },
    past_due: { label: 'Past Due', class: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100' },
    cancelled: { label: 'Cancelled', class: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100' },
    suspended: { label: 'Suspended', class: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }
}

const statusLabel = computed(() => statusMap[props.status]?.label || props.status)
const badgeClasses = computed(() => statusMap[props.status]?.class || 'bg-gray-100 text-gray-800')
</script>