<script setup lang="ts">
import Pagination from '@/Components/Pagination.vue'
import SortableHeader from '@/Components/SortableHeader.vue'
import { useTranslate } from '@/composables/useTranslate'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import type { PaginatorMeta } from '@/Types/pagination'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import IconButton from '@/Components/IconButton.vue'
import { useConfirmDialog } from '@/composables/useConfirmDialog'
import { IconEdit, IconTrash, IconAdjustmentsAlt } from '@tabler/icons-vue'

const { t } = useTranslate()
const { confirm } = useConfirmDialog()

interface Friend {
  id: number
  first_name: string
  last_name: string
  email: string | null
}

const props = defineProps<{
  friends: {
    data: Friend[]
    meta: PaginatorMeta
  }
}>()

const searchQuery = ref(props.friends.meta.search ?? '')
let debounceTimer: ReturnType<typeof setTimeout> | null = null

function onSearch(): void {
  if (debounceTimer) clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    router.get(
      route('friends.index'),
      {
        search: searchQuery.value,
        sort: props.friends.meta.sort,
        direction: props.friends.meta.direction,
        page: 1,
        per_page: props.friends.meta.per_page,
      },
      { preserveState: true, preserveScroll: false },
    )
  }, 300)
}

function clearSearch(): void {
  if (debounceTimer) clearTimeout(debounceTimer)
  searchQuery.value = ''
  router.get(
    route('friends.index'),
    {
      sort: props.friends.meta.sort,
      direction: props.friends.meta.direction,
      per_page: props.friends.meta.per_page,
    },
    { preserveState: true, preserveScroll: false },
  )
}

function sort(column: string): void {
  const newDirection =
    props.friends.meta.sort === column && props.friends.meta.direction === 'asc'
      ? 'desc'
      : 'asc'
  router.get(
    route('friends.index'),
    {
      sort: column,
      direction: newDirection,
      search: searchQuery.value,
      page: 1,
      per_page: props.friends.meta.per_page,
    },
    { preserveState: true, preserveScroll: true },
  )
}

async function deleteFriend(friend: Friend): Promise<void> {
  const confirmed = await confirm({
    title: t('friends.deleteTitle'),
    message: t('friends.deleteConfirm').replace('{name}', `${friend.first_name} ${friend.last_name}`),
    confirmLabel: t('common.delete'),
    cancelLabel: t('common.cancel'),
    variant: 'danger',
  })
  if (confirmed) {
    router.delete(route('friends.destroy', friend.id))
  }
}
</script>

<template>
  <Head :title="t('friends.title')" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2
          class="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200"
        >
          {{ t('friends.title') }}
        </h2>
        <Link
          :href="route('friends.create')"
          class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500"
        >
          {{ t('friends.add') }}
        </Link>
      </div>
    </template>

    <div class="py-6 sm:py-12">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div
          class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800"
        >
          <div
            class="flex items-center gap-3 border-b border-gray-200 px-4 py-3 sm:px-6 dark:border-gray-700"
          >
            <input
              v-model="searchQuery"
              type="search"
              :placeholder="t('friends.searchPlaceholder')"
              class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:max-w-xs dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:placeholder:text-gray-400"
              @input="onSearch"
            >
            <button
              v-if="searchQuery"
              class="rounded-md border border-transparent bg-gray-200 px-3 py-1.5 text-sm font-medium leading-6 text-gray-700 hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500"
              @click="clearSearch"
            >
              {{ t('friends.clearSearch') }}
            </button>
          </div>

          <div
            v-if="friends.meta.total === 0"
            class="p-4 text-gray-500 sm:p-6 dark:text-gray-400"
          >
            {{ searchQuery ? t('friends.noResults') : t('friends.empty') }}
          </div>

          <template v-else>
            <table class="hidden w-full text-left text-sm sm:table">
              <thead
                class="border-b bg-gray-50 text-xs text-gray-700 uppercase dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400"
              >
                <tr>
                  <SortableHeader
                    column="last_name"
                    :label="t('friends.lastName')"
                    :current-sort="friends.meta.sort!"
                    :current-direction="friends.meta.direction!"
                    @sort="sort"
                  />
                  <SortableHeader
                    column="first_name"
                    :label="t('friends.firstName')"
                    :current-sort="friends.meta.sort!"
                    :current-direction="friends.meta.direction!"
                    @sort="sort"
                  />
                  <SortableHeader
                    column="email"
                    :label="t('friends.email')"
                    :current-sort="friends.meta.sort!"
                    :current-direction="friends.meta.direction!"
                    @sort="sort"
                  />
                  <th class="px-6 py-3 text-right">
                    {{ t('friends.actions') }}
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="friend in friends.data"
                  :key="friend.id"
                  class="border-b dark:border-gray-700"
                >
                  <td
                    class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100"
                  >
                    {{ friend.last_name }}
                  </td>
                  <td
                    class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100"
                  >
                    {{ friend.first_name }}
                  </td>
                  <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                    {{ friend.email || '—' }}
                  </td>
                  <td class="px-6 py-4 text-right">
                    <IconButton
                      :icon="IconAdjustmentsAlt"
                      :label="t('friends.preferences')"
                      variant="preference"
                      @click="router.visit(route('preferences.show', friend.id))"
                    />
                    <IconButton
                      :icon="IconEdit"
                      :label="t('friends.edit')"
                      variant="default"
                      @click="router.visit(route('friends.edit', friend.id))"
                    />
                    <IconButton
                      :icon="IconTrash"
                      :label="t('friends.delete')"
                      variant="danger"
                      @click="deleteFriend(friend)"
                    />
                  </td>
                </tr>
              </tbody>
            </table>

            <div class="divide-y divide-gray-100 sm:hidden dark:divide-gray-700">
              <div
                v-for="friend in friends.data"
                :key="friend.id"
                class="space-y-2 p-4"
              >
                <p class="font-medium text-gray-900 dark:text-gray-100">
                  {{ friend.first_name }} {{ friend.last_name }}
                </p>
                <p v-if="friend.email" class="text-sm text-gray-600 dark:text-gray-400">
                  {{ friend.email }}
                </p>
                <div class="flex gap-4 pt-1">
                  <IconButton
                    :icon="IconAdjustmentsAlt"
                    :label="t('friends.preferences')"
                    variant="preference"
                    @click="router.visit(route('preferences.show', friend.id))"
                  />
                  <IconButton
                    :icon="IconEdit"
                    :label="t('friends.edit')"
                    variant="default"
                    @click="router.visit(route('friends.edit', friend.id))"
                  />
                  <IconButton
                    :icon="IconTrash"
                    :label="t('friends.delete')"
                    variant="danger"
                    @click="deleteFriend(friend)"
                  />
                </div>
              </div>
            </div>

            <Pagination
              :meta="friends.meta"
              route-name="friends.index"
              :extra-params="{ search: searchQuery, ...(friends.meta.sort ? { sort: friends.meta.sort } : {}), ...(friends.meta.direction ? { direction: friends.meta.direction } : {}) }"
            />
          </template>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
