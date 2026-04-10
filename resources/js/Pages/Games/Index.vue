<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import SortableHeader from '@/Components/SortableHeader.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { useTranslate } from '@/composables/useTranslate'
import { ref } from 'vue'
import type { PaginatorMeta } from '@/Types/pagination'
import IconButton from '@/Components/IconButton.vue'
import { useConfirmDialog } from '@/composables/useConfirmDialog'
import { IconEdit, IconTrash } from '@tabler/icons-vue'

const { t } = useTranslate()
const { confirm } = useConfirmDialog()

interface Game {
  id: number
  name: string
  min_players: number
  max_players: number
  is_shared: boolean
  user_id: number | null
  description: string | null
  year: number | null
  copies: number
}

const props = defineProps<{
  games: {
    data: Game[]
    meta: PaginatorMeta
  }
}>()

const searchQuery = ref(props.games.meta.search ?? '')
const playersFilter = ref<number | ''>(props.games.meta.players ?? '')
let debounceTimer: ReturnType<typeof setTimeout> | null = null

function navigate(): void {
  if (debounceTimer) clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    router.get(
      route('games.index'),
      {
        search: searchQuery.value,
        players: playersFilter.value !== '' ? playersFilter.value : undefined,
        sort: props.games.meta.sort,
        direction: props.games.meta.direction,
        page: 1,
        per_page: props.games.meta.per_page,
      },
      { preserveState: true, preserveScroll: false },
    )
  }, 300)
}

function clearFilters(): void {
  if (debounceTimer) clearTimeout(debounceTimer)
  searchQuery.value = ''
  playersFilter.value = ''
  router.get(
    route('games.index'),
    { sort: props.games.meta.sort, direction: props.games.meta.direction, per_page: props.games.meta.per_page },
    { preserveState: true, preserveScroll: false },
  )
}

const hasActiveFilters = () =>
  searchQuery.value !== '' || playersFilter.value !== ''

function sort(column: string): void {
  const newDirection =
    props.games.meta.sort === column && props.games.meta.direction === 'asc'
      ? 'desc'
      : 'asc'
  router.get(
    route('games.index'),
    {
      sort: column,
      direction: newDirection,
      search: searchQuery.value,
      players: playersFilter.value !== '' ? playersFilter.value : undefined,
      page: 1,
      per_page: props.games.meta.per_page,
    },
    { preserveState: true, preserveScroll: true },
  )
}

const expandedDescriptions = ref<Set<number>>(new Set())

function toggleDescription(id: number): void {
  const next = new Set(expandedDescriptions.value)
  if (next.has(id)) {
    next.delete(id)
  } else {
    next.add(id)
  }
  expandedDescriptions.value = next
}

async function deleteGame(game: Game): Promise<void> {
  const confirmed = await confirm({
    title: t('games.deleteTitle'),
    message: t('games.deleteConfirm').replace('{name}', game.name),
    confirmLabel: t('common.delete'),
    cancelLabel: t('common.cancel'),
    variant: 'danger',
  })

  if (confirmed) {
    router.delete(route('games.destroy', game.id))
  }
}
</script>

<template>
  <Head :title="t('games.title')" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
          {{ t('games.title') }}
        </h2>
        <Link
          :href="route('games.create')"
          class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500"
        >
          {{ t('games.add') }}
        </Link>
      </div>
    </template>

    <div class="py-6 sm:py-12">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
          <div class="flex flex-wrap items-end gap-3 border-b border-gray-200 px-4 py-3 dark:border-gray-700 sm:px-6">
            <div class="flex-1 min-w-45">
              <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">
                {{ t('games.searchLabel') }}
              </label>
              <input
                v-model="searchQuery"
                type="search"
                :placeholder="t('games.searchPlaceholder')"
                class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:placeholder:text-gray-400"
                @input="navigate"
              >
            </div>
            <div class="w-66">
              <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">
                {{ t('games.playersFilterLabel') }}
              </label>
              <input
                v-model.number="playersFilter"
                type="number"
                min="1"
                :placeholder="t('games.playersFilterPlaceholder')"
                class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:placeholder:text-gray-400"
                @input="navigate"
              >
            </div>
            <button
              v-if="hasActiveFilters()"
              class="self-end rounded-md border border-transparent bg-gray-200 px-3 py-1.5 text-sm font-medium leading-6 text-gray-700 hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500"
              @click="clearFilters"
            >
              {{ t('games.clearFilters') }}
            </button>
          </div>

          <div v-if="games.meta.total === 0" class="p-4 text-gray-500 sm:p-6 dark:text-gray-400">
            {{ hasActiveFilters() ? t('games.noResults') : t('games.empty') }}
          </div>

          <template v-else>
            <table class="hidden w-full text-left text-sm sm:table">
              <thead class="border-b bg-gray-50 text-xs uppercase text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                <tr>
                  <SortableHeader
                    column="name"
                    :label="t('games.name')"
                    :current-sort="games.meta.sort!"
                    :current-direction="games.meta.direction!"
                    @sort="sort"
                  />
                  <SortableHeader
                    column="min_players"
                    :label="t('games.players')"
                    :current-sort="games.meta.sort!"
                    :current-direction="games.meta.direction!"
                    @sort="sort"
                  />
                  <SortableHeader
                    column="copies"
                    :label="t('games.copies')"
                    :current-sort="games.meta.sort!"
                    :current-direction="games.meta.direction!"
                    @sort="sort"
                  />
                  <SortableHeader
                    column="year"
                    :label="t('games.year')"
                    :current-sort="games.meta.sort!"
                    :current-direction="games.meta.direction!"
                    @sort="sort"
                  />
                  <th class="px-6 py-3">{{ t('games.type') }}</th>
                  <th class="px-6 py-3 text-right">{{ t('games.actions') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="game in games.data"
                  :key="game.id"
                  class="border-b dark:border-gray-700"
                >
                  <td class="px-6 py-4">
                    <div class="font-medium text-gray-900 dark:text-gray-100">
                      {{ game.name }}
                    </div>
                    <template v-if="game.description">
                      <p
                        class="mt-0.5 max-w-sm whitespace-pre-line text-xs text-gray-400 dark:text-gray-500"
                        :class="expandedDescriptions.has(game.id) ? '' : 'line-clamp-1'"
                      >
                        {{ game.description }}
                      </p>
                      <button
                        class="mt-0.5 text-xs text-indigo-500 hover:text-indigo-700 dark:text-indigo-400"
                        @click="toggleDescription(game.id)"
                      >
                        {{ expandedDescriptions.has(game.id) ? t('games.showLess') : t('games.showMore') }}
                      </button>
                    </template>
                  </td>
                  <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                    {{ game.min_players }}–{{ game.max_players }}
                  </td>
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                      {{ game.copies }}×
                    </span>
                  </td>
                  <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                    {{ game.year ?? '—' }}
                  </td>
                  <td class="px-6 py-4">
                    <span
                      v-if="game.is_shared"
                      class="rounded bg-green-100 px-2 py-1 text-xs text-green-800 dark:bg-green-900 dark:text-green-300"
                    >
                      {{ t('games.shared') }}
                    </span>
                    <span
                      v-else
                      class="rounded bg-blue-100 px-2 py-1 text-xs text-blue-800 dark:bg-blue-900 dark:text-blue-300"
                    >
                      {{ t('games.mine') }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <template v-if="!game.is_shared">
                      <IconButton
                        :icon="IconEdit"
                        :label="t('games.edit')"
                        variant="default"
                        @click="router.visit(route('games.edit', game.id))"
                      />
                      <IconButton
                        :icon="IconTrash"
                        :label="t('games.delete')"
                        variant="danger"
                        @click="deleteGame(game)"
                      />
                    </template>
                    <span v-else class="text-gray-400 dark:text-gray-500">—</span>
                  </td>
                </tr>
              </tbody>
            </table>

            <div class="divide-y dark:divide-gray-700 sm:hidden">
              <div
                v-for="game in games.data"
                :key="game.id"
                class="space-y-2 p-4"
              >
                <div class="flex items-center justify-between">
                  <div>
                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ game.name }}</span>
                    <span v-if="game.year" class="ml-1 text-xs text-gray-400">({{ game.year }})</span>
                  </div>
                  <span
                    v-if="game.is_shared"
                    class="rounded bg-green-100 px-2 py-1 text-xs text-green-800 dark:bg-green-900 dark:text-green-300"
                  >
                    {{ t('games.shared') }}
                  </span>
                  <span
                    v-else
                    class="rounded bg-blue-100 px-2 py-1 text-xs text-blue-800 dark:bg-blue-900 dark:text-blue-300"
                  >
                    {{ t('games.mine') }}
                  </span>
                </div>
                <template v-if="game.description">
                  <p
                    class="whitespace-pre-line text-xs text-gray-400 dark:text-gray-500"
                    :class="expandedDescriptions.has(game.id) ? '' : 'line-clamp-2'"
                  >
                    {{ game.description }}
                  </p>
                  <button
                    class="text-xs text-indigo-500 hover:text-indigo-700 dark:text-indigo-400"
                    @click="toggleDescription(game.id)"
                  >
                    {{ expandedDescriptions.has(game.id) ? t('games.showLess') : t('games.showMore') }}
                  </button>
                </template>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  {{ game.min_players }}–{{ game.max_players }} {{ t('games.playersCount') }}
                  · {{ game.copies }}× {{ t('games.copiesCount') }}
                </p>
                <div class="flex gap-4 pt-1">
                  <template v-if="!game.is_shared">
                    <IconButton
                      :icon="IconEdit"
                      :label="t('games.edit')"
                      variant="default"
                      @click="router.visit(route('games.edit', game.id))"
                    />
                    <IconButton
                      :icon="IconTrash"
                      :label="t('games.delete')"
                      variant="danger"
                      @click="deleteGame(game)"
                    />
                  </template>
                  <span v-else class="text-gray-400 dark:text-gray-500">—</span>
                </div>
              </div>
            </div>

            <Pagination
              :meta="games.meta"
              route-name="games.index"
            />
          </template>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
