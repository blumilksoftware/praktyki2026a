<script setup lang="ts">

import { IconArrowsUpDown, IconArrowDown, IconArrowUp } from '@tabler/icons-vue'

defineProps<{
  column: string
  label: string
  currentSort: string
  currentDirection: 'asc' | 'desc'
}>()

defineEmits<{
  sort: [column: string]
}>()
</script>

<template>
  <th
    class="cursor-pointer select-none px-6 py-3 hover:bg-gray-100 dark:hover:bg-gray-800"
    @click="$emit('sort', column)"
  >
    <span class="flex items-center gap-1">
      {{ label }}
      <span v-if="currentSort === column" class="text-indigo-500">
        <IconArrowUp v-if="currentDirection === 'asc'" />
        <IconArrowDown v-else />
      </span>
      <span v-else class="text-gray-300 dark:text-gray-600">
        <IconArrowsUpDown />
      </span>
    </span>
  </th>
</template>
