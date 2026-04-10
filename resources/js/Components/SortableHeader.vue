<script setup lang="ts">
import {
  IconCaretUpDownFilled,
  IconCaretDownFilled,
  IconCaretUpFilled,
} from '@tabler/icons-vue'

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
    class="cursor-pointer px-6 py-3 select-none hover:bg-gray-100 dark:hover:bg-gray-800"
    @click="$emit('sort', column)"
  >
    <span class="flex items-center gap-1">
      {{ label }}
      <span v-if="currentSort === column" class="text-indigo-500">
        <IconCaretUpFilled v-if="currentDirection === 'asc'" />
        <IconCaretDownFilled v-else />
      </span>
      <span v-else class="text-gray-300 dark:text-gray-600">
        <IconCaretUpDownFilled />
      </span>
    </span>
  </th>
</template>
