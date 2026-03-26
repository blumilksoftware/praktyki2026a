<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import SortableHeader from '@/Components/SortableHeader.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { useTranslate } from '@/composables/useTranslate'
import { ref } from 'vue'
import type { PaginatorMeta } from '@/types/pagination'

const { t } = useTranslate()

interface Friend {
  id: number
  first_name: string
  last_name: string
}

interface Game {
  id: number
  name: string
}

interface Session {
  id: number
  name: string
  date: string
  notes: string | null
  friends: Friend[]
  games: Game[]
}

const props = defineProps<{
  sessions: {
    data: Session[]
    meta: PaginatorMeta
  }
}>()

// ── Search and date filter state ───────────────────────────────────────────
const searchQuery = ref(props.sessions.meta.search ?? '')
const dateFrom = ref(props.sessions.meta.date_from ?? '')
const dateTo = ref(props.sessions.meta.date_to ?? '')
let debounceTimer: ReturnType<typeof setTimeout> | null = null

function navigate(): void {
  if (debounceTimer) clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    router.get(
      route('sessions.index'),
      {
        search: searchQuery.value,
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
        sort: props.sessions.meta.sort,
        direction: props.sessions.meta.direction,
        page: 1,
        per_page: props.sessions.meta.per_page,
      },
      { preserveState: true, preserveScroll: false },
    )
  }, 300)
}

function clearFilters(): void {
  if (debounceTimer) clearTimeout(debounceTimer)
  searchQuery.value = ''
  dateFrom.value = ''
  dateTo.value = ''
  router.get(
    route('sessions.index'),
    { sort: props.sessions.meta.sort, direction: props.sessions.meta.direction, per_page: props.sessions.meta.per_page },
    { preserveState: true, preserveScroll: false },
  )
}

const hasActiveFilters = () =>
  searchQuery.value !== '' || dateFrom.value !== '' || dateTo.value !== ''

// ── Sorting ────────────────────────────────────────────────────────────────
function sort(column: string): void {
  // Sessions default to descending, so flipping the active column goes
  // from desc → asc, unlike Games which goes asc → desc.
  const newDirection =
    props.sessions.meta.sort === column && props.sessions.meta.direction === 'desc'
      ? 'asc'
      : 'desc'
  router.get(
    route('sessions.index'),
    {
      sort: column,
      direction: newDirection,
      search: searchQuery.value,
      date_from: dateFrom.value || undefined,
      date_to: dateTo.value || undefined,
      page: 1,
      per_page: props.sessions.meta.per_page,
    },
    { preserveState: true, preserveScroll: true },
  )
}

// ── Delete ─────────────────────────────────────────────────────────────────
function deleteSession(session: Session): void {
  const translations = (usePage().props as Record<string, unknown>)
    .translations as Record<string, string>
  const msg = (
    translations?.['sessions.deleteConfirm'] ?? 'Delete "{name}"?'
  ).replace('{name}', session.name)
  if (confirm(msg)) {
    router.delete(route('sessions.destroy', session.id))
  }
}
</script>

<template>
  <Head :title="t('sessions.title')" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
          {{ t('sessions.title') }}
        </h2>
        <Link
          :href="route('sessions.create')"
          class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500"
        >
          {{ t('sessions.add') }}
        </Link>
      </div>
    </template>

    <div class="py-6 sm:py-12">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">

          <!-- Search and date filter bar -->
          <div class="flex flex-wrap items-end gap-3 border-b border-gray-200 px-4 py-3 dark:border-gray-700 sm:px-6">
            <div class="flex-1 min-w-[180px]">
              <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">
                {{ t('sessions.searchLabel') }}
              </label>
              <input
                v-model="searchQuery"
                type="search"
                :placeholder="t('sessions.searchPlaceholder')"
                class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:placeholder-gray-400"
                @input="navigate"
              >
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">
                {{ t('sessions.dateFrom') }}
              </label>
              <input
                v-model="dateFrom"
                type="date"
                class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                @change="navigate"
              >
            </div>
            <div>
              <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">
                {{ t('sessions.dateTo') }}
              </label>
              <input
                v-model="dateTo"
                type="date"
                class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200"
                @change="navigate"
              >
            </div>
            <button
              v-if="hasActiveFilters()"
              class="text-sm text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
              @click="clearFilters"
            >
              {{ t('sessions.clearFilters') }}
            </button>
          </div>

          <div
            v-if="sessions.meta.total === 0"
            class="p-4 text-gray-500 sm:p-6 dark:text-gray-400"
          >
            {{ hasActiveFilters() ? t('sessions.noResults') : t('sessions.empty') }}
          </div>

          <template v-else>
            <!-- Desktop table -->
            <table class="hidden w-full text-left text-sm sm:table">
              <thead class="border-b bg-gray-50 text-xs uppercase text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                <tr>
                  <SortableHeader
                    column="name"
                    :label="t('sessions.name')"
                    :current-sort="sessions.meta.sort!"
                    :current-direction="sessions.meta.direction!"
                    @sort="sort"
                  />
                  <SortableHeader
                    column="date"
                    :label="t('sessions.date')"
                    :current-sort="sessions.meta.sort!"
                    :current-direction="sessions.meta.direction!"
                    @sort="sort"
                  />
                  <th class="px-6 py-3">{{ t('sessions.friends') }}</th>
                  <th class="px-6 py-3">{{ t('sessions.games') }}</th>
                  <th class="px-6 py-3 text-right">{{ t('sessions.actions') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="session in sessions.data"
                  :key="session.id"
                  class="border-b dark:border-gray-700"
                >
                  <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">
                    <Link
                      :href="route('sessions.show', session.id)"
                      class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400"
                    >
                      {{ session.name }}
                    </Link>
                  </td>
                  <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                    {{ session.date }}
                  </td>
                  <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                    {{ session.friends.length }}
                  </td>
                  <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                    {{ session.games.length }}
                  </td>
                  <td class="px-6 py-4 text-right">
                    <Link
                      :href="route('sessions.edit', session.id)"
                      class="mr-3 text-indigo-600 hover:text-indigo-900 dark:text-indigo-400"
                    >
                      {{ t('sessions.edit') }}
                    </Link>
                    <button
                      class="text-red-600 hover:text-red-900 dark:text-red-400"
                      @click="deleteSession(session)"
                    >
                      {{ t('sessions.delete') }}
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>

            <!-- Mobile cards -->
            <div class="divide-y dark:divide-gray-700 sm:hidden">
              <div
                v-for="session in sessions.data"
                :key="session.id"
                class="space-y-2 p-4"
              >
                <div class="flex items-center justify-between">
                  <Link
                    :href="route('sessions.show', session.id)"
                    class="font-medium text-indigo-600 hover:text-indigo-900 dark:text-indigo-400"
                  >
                    {{ session.name }}
                  </Link>
                  <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ session.date }}
                  </span>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  {{ session.friends.length }} {{ t('sessions.friendsCount') }},
                  {{ session.games.length }} {{ t('sessions.gamesCount') }}
                </p>
                <div class="flex gap-4 pt-1">
                  <Link
                    :href="route('sessions.edit', session.id)"
                    class="text-sm font-medium text-indigo-600 hover:text-indigo-900 dark:text-indigo-400"
                  >
                    {{ t('sessions.edit') }}
                  </Link>
                  <button
                    class="text-sm font-medium text-red-600 hover:text-red-900 dark:text-red-400"
                    @click="deleteSession(session)"
                  >
                    {{ t('sessions.delete') }}
                  </button>
                </div>
              </div>
            </div>

            <Pagination
              :meta="sessions.meta"
              route-name="sessions.index"
            />
          </template>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
