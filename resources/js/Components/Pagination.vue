<template>
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <span class="text-sm text-gray-700 dark:text-gray-300">
                Showing <span class="font-medium">{{ from }}</span> to <span class="font-medium">{{ to }}</span> of
                <span class="font-medium">{{ total }}</span> results
            </span>
        </div>
        <div class="flex items-center space-x-2">
            <Link
                v-for="link in links"
                :key="link.label"
                :href="link.url || '#'"
                v-html="link.label"
                :class="[
                    'px-3 py-1 rounded-lg text-sm',
                    link.active 
                        ? 'bg-primary-600 text-white' 
                        : link.url 
                            ? 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-600'
                            : 'bg-gray-100 dark:bg-gray-800 text-gray-400 cursor-not-allowed'
                ]"
            />
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    links: Array
})

const from = computed(() => {
    if (!props.links) return 0
    const match = props.links[0]?.label?.match(/Showing (\d+) to (\d+) of (\d+)/)
    return match ? parseInt(match[1]) : 0
})

const to = computed(() => {
    if (!props.links) return 0
    const match = props.links[0]?.label?.match(/Showing (\d+) to (\d+) of (\d+)/)
    return match ? parseInt(match[2]) : 0
})

const total = computed(() => {
    if (!props.links) return 0
    const match = props.links[0]?.label?.match(/Showing (\d+) to (\d+) of (\d+)/)
    return match ? parseInt(match[3]) : 0
})
</script>