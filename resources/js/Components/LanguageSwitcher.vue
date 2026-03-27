<script setup lang="ts">
import Dropdown from '@/Components/Dropdown.vue'
import { router, usePage } from '@inertiajs/vue3'

const page = usePage()

const languages = [
  { code: 'pl', label: 'Polski' },
  { code: 'en', label: 'English' },
]

function switchLocale(code: string) {
  if (code !== page.props.locale) {
    router.post(route('locale.switch', code), {}, {
      preserveScroll: true,
    })
  }
}
</script>

<template>
  <Dropdown align="right" width="48">
    <template #trigger>
      <button
        type="button"
        class="inline-flex items-center rounded-md px-2 py-1 text-sm font-medium text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-300"
      >
        {{ (page.props.locale as string).toUpperCase() }}
        <svg class="-me-0.5 ms-1 size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
      </button>
    </template>

    <template #content>
      <button
        v-for="lang in languages"
        :key="lang.code"
        type="button"
        class="block w-full px-4 py-2 text-start text-sm leading-5 transition duration-150 ease-in-out"
        :class="page.props.locale === lang.code
          ? 'font-semibold text-gray-900 bg-gray-100 dark:text-gray-100 dark:bg-gray-800'
          : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'"
        @click="switchLocale(lang.code)"
      >
        {{ lang.label }}
      </button>
    </template>
  </Dropdown>
</template>
