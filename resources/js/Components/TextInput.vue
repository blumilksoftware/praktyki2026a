<script setup>
import { onMounted, ref } from 'vue'

const model = defineModel({
  type: [String, Number],
  required: true,
})

defineProps({
  invalid: Boolean,
})

const input = ref(null)

onMounted(() => {
  if (input.value.hasAttribute('autofocus')) {
    input.value.focus()
  }
})

defineExpose({ focus: () => input.value.focus() })
</script>

<template>
  <input
    ref="input"
    v-model="model"
    class="rounded-md shadow-sm focus:ring-indigo-500 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600"
    :class="invalid
      ? 'border-red-500 focus:border-red-500 dark:border-red-500'
      : 'border-gray-300 focus:border-indigo-500 dark:border-gray-700'"
  >
</template>
