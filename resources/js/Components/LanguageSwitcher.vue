<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3'

const page = usePage()

function switchLocale() {
  const newLocale = page.props.locale === 'pl' ? 'en' : 'pl'
  router.post(route('locale.switch', newLocale), {}, {
    preserveScroll: true,
  })
}
</script>

<template>
  <button
    type="button"
    class="rounded-md px-2 py-1 text-sm font-medium text-gray-500 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
    @click="switchLocale"
  >
    {{ page.props.locale === 'pl' ? 'EN' : 'PL' }}
  </button>
</template>
