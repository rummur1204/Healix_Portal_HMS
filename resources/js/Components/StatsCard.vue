<template>
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ title }}</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ value }}</p>
            </div>
            <div class="p-3 rounded-lg" :class="iconBgClass">
                <component :is="icon" class="w-6 h-6" :class="iconColorClass" />
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: String,
    value: [String, Number],
    icon: Object,
    iconBgClass: String,
    iconColorClass: String
})
</script>