<script setup lang="ts">
import type { Component } from 'vue'

defineProps<{
  icon: Component
  label: string
  variant?: 'default' | 'danger' | 'preference'
  disabled?: boolean
}>()

defineEmits<{
  click: []
}>()
</script>

<template>
  <button
    type="button"
    :title="label"
    :aria-label="label"
    :disabled="disabled"
    class="cursor-pointer rounded p-1 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1"
    :class="{
      'text-indigo-600 hover:bg-indigo-50 hover:text-indigo-800 focus:ring-indigo-500 dark:text-indigo-400 dark:hover:bg-indigo-500/50':
        variant == 'default',
      'text-red-600 hover:bg-red-50 hover:text-red-800 focus:ring-red-500 dark:text-red-400 dark:hover:bg-red-900/30':
        variant === 'danger',
      'text-green-600 hover:bg-green-50 hover:text-green-800 focus:ring-green-500 dark:text-green-400 dark:hover:bg-green-900/30':
        variant === 'preference',
      'opacity-40 cursor-not-allowed': disabled,
    }"
    @click="$emit('click')"
  >
    <component :is="icon" :size="18" :stroke-width="1.5" />
  </button>
</template>
