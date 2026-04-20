<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { useTranslate } from '@/composables/useTranslate'

const { t } = useTranslate()

interface PaginatorMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number | null
  to: number | null
}

const props = defineProps<{
  meta: PaginatorMeta
  routeName: string
  extraParams?: Record<string, string | number>
}>()

const perPageOptions = [10, 25, 50]

function goToPage(page: number): void {
  if (page < 1 || page > props.meta.last_page) return

  router.get(
    route(props.routeName),
    {
      ...props.extraParams,
      page,
      per_page: props.meta.per_page,
    },
    { preserveState: true, preserveScroll: true },
  )
}

function changePerPage(event: Event): void {
  const perPage = parseInt((event.target as HTMLSelectElement).value, 10)

  router.get(
    route(props.routeName),
    {
      ...props.extraParams,
      page: 1,
      per_page: perPage,
    },
    { preserveState: true, preserveScroll: true },
  )
}

function pageWindow(): Array<number | '...'> {
  const total = props.meta.last_page
  const current = props.meta.current_page

  if (total <= 7) {
    return Array.from({ length: total }, (_, i) => i + 1)
  }

  const pages: Array<number | '...'> = [1]

  if (current > 3) pages.push('...')

  for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) {
    pages.push(i)
  }

  if (current < total - 2) pages.push('...')

  pages.push(total)

  return pages
}
</script>

<template>
  <div class="flex flex-col items-center justify-between gap-4 border-t border-gray-200 px-4 py-3 dark:border-gray-700 sm:flex-row sm:px-6">
    <div class="flex items-center gap-4">
      <p class="text-sm text-gray-500 dark:text-gray-400">
        <template v-if="meta.from && meta.to">
          {{ t('pagination.showing') }}
          <span class="font-medium text-gray-700 dark:text-gray-300">{{ meta.from }}</span>
          {{ t('pagination.to') }}
          <span class="font-medium text-gray-700 dark:text-gray-300">{{ meta.to }}</span>
          {{ t('pagination.of') }}
          <span class="font-medium text-gray-700 dark:text-gray-300">{{ meta.total }}</span>
          {{ t('pagination.results') }}
        </template>
        <template v-else>
          {{ t('pagination.noResults') }}
        </template>
      </p>

      <label class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
        {{ t('pagination.perPage') }}
        <select
          :value="meta.per_page"
          class="cursor-pointer rounded-md border-gray-300 py-1 pl-2 pr-7 text-sm text-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
          @change="changePerPage"
        >
          <option
            v-for="option in perPageOptions"
            :key="option"
            :value="option"
            class="cursor-pointer"
          >
            {{ option }}
          </option>
        </select>
      </label>
    </div>

    <div class="flex items-center gap-1">
      <button
        class="cursor-pointer rounded px-2 py-1 text-sm text-gray-500 hover:bg-gray-100 disabled:cursor-default disabled:opacity-40 dark:text-gray-400 dark:hover:bg-gray-700"
        :disabled="meta.current_page === 1"
        @click="goToPage(meta.current_page - 1)"
      >
        ←
      </button>

      <template v-for="page in pageWindow()" :key="page">
        <span
          v-if="page === '...'"
          class="px-2 py-1 text-sm text-gray-400"
        >
          …
        </span>
        <button
          v-else
          class="min-w-8 rounded px-2 py-1 text-sm transition-colors"
          :class="page === meta.current_page
            ? 'cursor-default bg-indigo-600 text-white font-medium'
            : 'cursor-pointer text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700'"
          @click="goToPage(page)"
        >
          {{ page }}
        </button>
      </template>

      <button
        class="cursor-pointer rounded px-2 py-1 text-sm text-gray-500 hover:bg-gray-100 disabled:cursor-default disabled:opacity-40 dark:text-gray-400 dark:hover:bg-gray-700"
        :disabled="meta.current_page === meta.last_page"
        @click="goToPage(meta.current_page + 1)"
      >
        →
      </button>
    </div>
  </div>
</template>
