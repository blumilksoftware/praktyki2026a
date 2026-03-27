<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { useTranslate } from '@/composables/useTranslate'

const { t } = useTranslate()

interface Game {
    id: number
    name: string
    min_players: number
    max_players: number
    is_shared: boolean
    user_id: number | null
}

defineProps<{
    games: Game[]
}>()

function deleteGame(game: Game) {
  const translations = (usePage().props as Record<string, unknown>).translations as Record<string, string>
  const msg = (translations?.['games.deleteConfirm'] ?? 'Delete "{name}"?').replace('{name}', game.name)
  if (confirm(msg)) {
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
          <div v-if="games.length === 0" class="p-4 sm:p-6 text-gray-500 dark:text-gray-400">
            {{ t('games.empty') }}
          </div>

          <template v-else>
            <table class="hidden w-full text-left text-sm sm:table">
              <thead class="border-b bg-gray-50 text-xs uppercase text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                <tr>
                  <th class="px-6 py-3">{{ t('games.name') }}</th>
                  <th class="px-6 py-3">{{ t('games.players') }}</th>
                  <th class="px-6 py-3">{{ t('games.type') }}</th>
                  <th class="px-6 py-3 text-right">{{ t('games.actions') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="game in games"
                  :key="game.id"
                  class="border-b border-gray-100 transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/50"
                >
                  <td class="px-6 py-4 font-medium">
                    <Link :href="route('games.show', game.id)" class="text-indigo-600 hover:text-indigo-500 hover:underline dark:text-indigo-400 dark:hover:text-indigo-300">
                      {{ game.name }}
                    </Link>
                  </td>
                  <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                    {{ game.min_players }}–{{ game.max_players }}
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
                      <Link
                        :href="route('games.edit', game.id)"
                        :title="t('games.edit')"
                        class="mr-3 inline-block text-indigo-600 hover:text-indigo-500 hover:underline dark:text-indigo-400 dark:hover:text-indigo-300"
                      >
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                        </svg>
                      </Link>
                      <button
                        :title="t('games.delete')"
                        class="inline-block text-red-600 hover:text-red-500 hover:underline dark:text-red-400 dark:hover:text-red-300"
                        @click="deleteGame(game)"
                      >
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                      </button>
                    </template>
                    <span v-else class="text-gray-400 dark:text-gray-500">—</span>
                  </td>
                </tr>
              </tbody>
            </table>

            <div class="divide-y divide-gray-100 dark:divide-gray-700 sm:hidden">
              <div
                v-for="game in games"
                :key="game.id"
                class="p-4 space-y-2"
              >
                <div class="flex items-center justify-between">
                  <Link :href="route('games.show', game.id)" class="font-medium text-indigo-600 hover:text-indigo-500 hover:underline dark:text-indigo-400 dark:hover:text-indigo-300">{{ game.name }}</Link>
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
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ game.min_players }}–{{ game.max_players }} {{ t('games.playersCount') }}</p>
                <div class="flex gap-4 pt-1">
                  <template v-if="!game.is_shared">
                    <Link
                      :href="route('games.edit', game.id)"
                      :title="t('games.edit')"
                      class="text-indigo-600 hover:text-indigo-500 hover:underline dark:text-indigo-400 dark:hover:text-indigo-300"
                    >
                      <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                      </svg>
                    </Link>
                    <button
                      :title="t('games.delete')"
                      class="text-red-600 hover:text-red-500 hover:underline dark:text-red-400 dark:hover:text-red-300"
                      @click="deleteGame(game)"
                    >
                      <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                      </svg>
                    </button>
                  </template>
                  <span v-else class="text-gray-400 dark:text-gray-500">—</span>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
