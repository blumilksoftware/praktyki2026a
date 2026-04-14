<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { useTranslate } from '@/composables/useTranslate'
import { IconCircleArrowLeftFilled } from '@tabler/icons-vue'

const { t } = useTranslate()

interface Game {
    id: number
    name: string
    min_players: number
    max_players: number
    is_shared: boolean
    user_id: number | null
    created_at: string
}

defineProps<{
    game: Game
}>()
</script>

<template>
  <Head :title="game.name" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
          {{ game.name }}
        </h2>
        <Link
          :href="route('games.index')"
          class="inline-flex items-center gap-1 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
        >
          <IconCircleArrowLeftFilled class="size-4" />
          {{ t('games.back') }}
        </Link>
      </div>
    </template>

    <div class="py-6 sm:py-12">
      <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
        <div class="bg-white p-4 shadow-sm sm:rounded-lg sm:p-6 dark:bg-gray-800">
          <dl class="divide-y divide-gray-100 dark:divide-gray-700">
            <div class="px-0 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('games.name') }}</dt>
              <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0 dark:text-gray-100">{{ game.name }}</dd>
            </div>
            <div class="px-0 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('games.players') }}</dt>
              <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0 dark:text-gray-100">{{ game.min_players }}–{{ game.max_players }}</dd>
            </div>
            <div class="px-0 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('games.type') }}</dt>
              <dd class="mt-1 sm:col-span-2 sm:mt-0">
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
              </dd>
            </div>
            <div class="px-0 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('games.owner') }}</dt>
              <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0 dark:text-gray-100">
                {{ game.is_shared ? t('games.system') : t('games.you') }}
              </dd>
            </div>
            <div class="px-0 py-4 sm:grid sm:grid-cols-3 sm:gap-4">
              <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('games.createdAt') }}</dt>
              <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0 dark:text-gray-100">
                {{ new Date(game.created_at).toLocaleDateString() }}
              </dd>
            </div>
          </dl>

          <div v-if="!game.is_shared" class="mt-6 flex justify-end gap-4">
            <Link
              :href="route('games.edit', game.id)"
              class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500"
            >
              {{ t('games.edit') }}
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
