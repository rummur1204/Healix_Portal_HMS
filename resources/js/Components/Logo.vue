<template>
    <div class="flex items-center space-x-2.5">
        <div class="relative">
            <!-- Logo icon - slightly smaller -->
            <div class="w-12 h-12 bg-gradient-to-br from-teal-400 to-teal-300 rounded-xl flex items-center justify-center shadow-lg border-2 border-white/30">
                <span class="text-primary-900 font-bold text-2xl">H+</span>
            </div>
            <!-- Status dot -->
            <div class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-teal-300 rounded-full border-2 border-primary-800"></div>
        </div>
        <div class="flex flex-col">
            <!-- Only the brand name - removed HMS Client Portal -->
            <span class="text-xl font-bold text-white drop-shadow">Healix-Plus</span>
        </div>
    </div>
</template>

<script setup>
// No props needed
</script>