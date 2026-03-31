<script setup lang="ts">
import IconButton from '@/Components/IconButton.vue'
import { useTranslate } from '@/composables/useTranslate'
import { useConfirmDialog } from '@/composables/useConfirmDialog'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
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
}

defineProps<{
  games: Game[]
}>()

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
        <h2 class="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200">
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
          <div
            v-if="games.length === 0"
            class="p-4 text-gray-500 sm:p-6 dark:text-gray-400"
          >
            {{ t('games.empty') }}
          </div>

          <template v-else>
            <table class="hidden w-full text-left text-sm sm:table">
              <thead class="border-b bg-gray-50 text-xs text-gray-700 uppercase dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
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
                    <Link
                      :href="route('games.show', game.id)"
                      class="text-indigo-600 hover:text-indigo-500 hover:underline dark:text-indigo-400 dark:hover:text-indigo-300"
                    >
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

            <div class="divide-y divide-gray-100 sm:hidden dark:divide-gray-700">
              <div v-for="game in games" :key="game.id" class="space-y-2 p-4">
                <div class="flex items-center justify-between">
                  <Link
                    :href="route('games.show', game.id)"
                    class="font-medium text-indigo-600 hover:text-indigo-500 hover:underline dark:text-indigo-400 dark:hover:text-indigo-300"
                  >
                    {{ game.name }}
                  </Link>
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
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  {{ game.min_players }}–{{ game.max_players }}
                  {{ t('games.playersCount') }}
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
          </template>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
