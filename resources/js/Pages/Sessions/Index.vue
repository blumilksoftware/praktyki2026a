<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import SortableHeader from '@/Components/SortableHeader.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { useTranslate } from '@/composables/useTranslate'
import { ref } from 'vue'
import type { PaginatorMeta } from '@/Types/pagination'
import IconButton from '@/Components/IconButton.vue'
import { useFormatDate } from '@/composables/useFormatDate'
import { useConfirmDialog } from '@/composables/useConfirmDialog'
import { IconEdit, IconTrash } from '@tabler/icons-vue'

const { t } = useTranslate()
const { confirm } = useConfirmDialog()
const { formatDate } = useFormatDate()

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

function sort(column: string): void {
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

async function deleteSession(session: Session): Promise<void> {
  const confirmed = await confirm({
    title: t('sessions.deleteTitle'),
    message: t('sessions.deleteConfirm').replace('{name}', session.name),
    confirmLabel: t('common.delete'),
    cancelLabel: t('common.cancel'),
    variant: 'danger',
  })

  if (confirmed) {
    router.delete(route('sessions.destroy', session.id))
  }
}
</script>

<template>
  <Head :title="t('sessions.title')" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200">
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
          <div class="flex flex-wrap items-end gap-3 border-b border-gray-200 px-4 py-3 dark:border-gray-700 sm:px-6">
            <div class="flex-1 min-w-[180px]">
              <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">
                {{ t('sessions.searchLabel') }}
              </label>
              <input
                v-model="searchQuery"
                type="search"
                :placeholder="t('sessions.searchPlaceholder')"
                class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:placeholder:text-gray-400"
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
              class="self-end rounded-md border border-transparent bg-gray-200 px-3 py-1.5 text-sm font-medium leading-6 text-gray-700 hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500"
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
                  class="border-b border-gray-100 transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/50"
                >
                  <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">
                    <Link
                      :href="route('sessions.show', session.id)"
                      class="text-indigo-600 hover:text-indigo-500 hover:underline dark:text-indigo-400 dark:hover:text-indigo-300"
                    >
                      {{ session.name }}
                    </Link>
                  </td>
                  <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                    {{ formatDate(session.date) }}
                  </td>
                  <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                    {{ session.friends.length }}
                  </td>
                  <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                    {{ session.games.length }}
                  </td>
                  <td class="px-6 py-4 text-right">
                    <IconButton
                      :icon="IconEdit"
                      :label="t('sessions.edit')"
                      variant="default"
                      @click="router.visit(route('sessions.edit', session.id))"
                    />
                    <IconButton
                      :icon="IconTrash"
                      :label="t('sessions.delete')"
                      variant="danger"
                      @click="deleteSession(session)"
                    />
                  </td>
                </tr>
              </tbody>
            </table>

            <div class="divide-y dark:divide-gray-700 sm:hidden">
              <div
                v-for="session in sessions.data"
                :key="session.id"
                class="space-y-2 p-4"
              >
                <div class="flex items-center justify-between">
                  <Link
                    :href="route('sessions.show', session.id)"
                    class="font-medium text-indigo-600 hover:text-indigo-500 hover:underline dark:text-indigo-400 dark:hover:text-indigo-300"
                  >
                    {{ session.name }}
                  </Link>
                  <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ formatDate(session.date) }}
                  </span>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  {{ session.friends.length }} {{ t('sessions.friendsCount') }},
                  {{ session.games.length }} {{ t('sessions.gamesCount') }}
                </p>
                <div class="flex gap-4 pt-1">
                  <IconButton
                    :icon="IconEdit"
                    :label="t('sessions.edit')"
                    variant="default"
                    @click="router.visit(route('sessions.edit', session.id))"
                  />
                  <IconButton
                    :icon="IconTrash"
                    :label="t('sessions.delete')"
                    variant="danger"
                    @click="deleteSession(session)"
                  />
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
