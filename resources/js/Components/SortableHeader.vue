<script setup lang="ts">

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
        {{ currentDirection === 'asc' ? '↑' : '↓' }}
      </span>
      <span v-else class="text-gray-300 dark:text-gray-600">↕</span>
    </span>
  </th>
</template>
