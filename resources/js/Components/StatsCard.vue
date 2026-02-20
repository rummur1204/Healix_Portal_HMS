<template>
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-teal-100 dark:border-teal-900 overflow-hidden hover:shadow-md transition-all duration-300">
        <div class="p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ title }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ value }}</p>
                </div>
                <div class="p-3 rounded-lg" :class="iconBgClass">
                    <component :is="icon" class="w-6 h-6" :class="iconColorClass" />
                </div>
            </div>
            
            <!-- Progress bar for certain stats -->
            <div v-if="progress !== undefined" class="mt-4">
                <div class="flex items-center justify-between text-xs mb-1">
                    <span class="text-gray-500 dark:text-gray-400">Progress</span>
                    <span class="text-teal-600 dark:text-teal-400 font-medium">{{ progress }}%</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div 
                        class="bg-gradient-to-r from-green-500 to-teal-500 rounded-full h-2 transition-all duration-500"
                        :style="{ width: progress + '%' }"
                    ></div>
                </div>
            </div>
            
            <div v-if="trend !== undefined" class="mt-4 flex items-center space-x-1">
                <ArrowUpIcon v-if="trend > 0" class="w-4 h-4 text-green-600" />
                <ArrowDownIcon v-if="trend < 0" class="w-4 h-4 text-red-600" />
                <span :class="trend > 0 ? 'text-green-600' : trend < 0 ? 'text-red-600' : 'text-gray-600'">
                    {{ Math.abs(trend) }}% from last month
                </span>
            </div>
            
            <div v-if="footer" class="mt-4 text-sm text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-800 pt-3">
                {{ footer }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { ArrowUpIcon, ArrowDownIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    title: String,
    value: [String, Number],
    icon: Object,
    iconBgClass: {
        type: String,
        default: 'bg-teal-50 dark:bg-teal-900/30'
    },
    iconColorClass: {
        type: String,
        default: 'text-teal-600 dark:text-teal-400'
    },
    progress: Number,
    trend: Number,
    footer: String,
})
</script>