<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { useTranslate } from '@/composables/useTranslate'
import { useFormatDate } from '@/composables/useFormatDate'
import { ref } from 'vue'

const { t } = useTranslate()
const { formatDate } = useFormatDate()

interface Friend {
    id: number
    first_name: string
    last_name: string
}

interface Game {
    id: number
    name: string
    min_players?: number
    max_players?: number
}

interface TableResult {
    game: Game
    friends: Friend[]
    avg_rating: number
}

interface Arrangement {
    tables: TableResult[]
    unseated: Friend[]
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
    session: Session
    arrangement?: Arrangement
}>()

const arranging = ref(false)
const showArrangement = ref(!!props.arrangement)

const canArrange = props.session.friends.length > 0 && props.session.games.length > 0

function arrange() {
  arranging.value = true
  router.post(route('sessions.arrange', props.session.id), {}, {
    preserveScroll: true,
    onFinish: () => {
      arranging.value = false
      showArrangement.value = true
    },
  })
}

function hideArrangement() {
  showArrangement.value = false
}
</script>

<template>
  <Head :title="session.name" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
          {{ session.name }}
        </h2>
        <div class="flex items-center gap-4">
          <Link
            :href="route('sessions.edit', session.id)"
            class="text-sm text-indigo-600 hover:text-indigo-500 hover:underline dark:text-indigo-400 dark:hover:text-indigo-300"
          >
            {{ t('sessions.edit') }}
          </Link>
          <Link
            :href="route('sessions.index')"
            class="text-sm text-gray-600 hover:text-gray-900 hover:underline dark:text-gray-400 dark:hover:text-gray-100"
          >
            {{ t('sessions.back') }}
          </Link>
        </div>
      </div>
    </template>

    <div class="py-6 sm:py-12">
      <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white p-4 shadow-sm sm:rounded-lg sm:p-6 dark:bg-gray-800">
          <div class="space-y-4">
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('sessions.date') }}</p>
              <p class="mt-1 text-gray-900 dark:text-gray-100">{{ formatDate(session.date) }}</p>
            </div>

            <div v-if="session.notes">
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('sessions.notes') }}</p>
              <p class="mt-1 text-gray-900 dark:text-gray-100">{{ session.notes }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white p-4 shadow-sm sm:rounded-lg sm:p-6 dark:bg-gray-800">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ t('sessions.friends') }}</h3>
          <div v-if="session.friends.length === 0" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            {{ t('sessions.noFriendsSelected') }}
          </div>
          <ul v-else class="mt-2 space-y-1">
            <li
              v-for="friend in session.friends"
              :key="friend.id"
              class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300"
            >
              {{ friend.first_name }} {{ friend.last_name }}
              <Link
                :href="route('preferences.show', friend.id) + '?redirect_to=' + encodeURIComponent(route('sessions.show', session.id))"
                class="text-xs text-indigo-500 hover:text-indigo-700 hover:underline dark:text-indigo-400 dark:hover:text-indigo-300"
              >
                {{ t('friends.preferences') }}
              </Link>
            </li>
          </ul>
        </div>

        <div class="bg-white p-4 shadow-sm sm:rounded-lg sm:p-6 dark:bg-gray-800">
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ t('sessions.games') }}</h3>
          <div v-if="session.games.length === 0" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            {{ t('sessions.noGamesSelected') }}
          </div>
          <ul v-else class="mt-2 space-y-1">
            <li
              v-for="game in session.games"
              :key="game.id"
              class="text-sm text-gray-700 dark:text-gray-300"
            >
              {{ game.name }}
            </li>
          </ul>
        </div>

        <div class="flex items-center gap-3">
          <button
            v-if="canArrange"
            :disabled="arranging"
            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
            @click="arrange"
          >
            {{ arranging ? t('sessions.arranging') : (arrangement ? t('sessions.rearrange') : t('sessions.arrange')) }}
          </button>
          <button
            v-if="arrangement && showArrangement"
            class="rounded-md bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:ring-gray-600 dark:hover:bg-gray-600"
            @click="hideArrangement"
          >
            {{ t('sessions.hideArrangement') }}
          </button>
          <p v-if="!canArrange" class="text-sm text-gray-500 dark:text-gray-400">
            {{ session.friends.length === 0 ? t('sessions.noArrangementFriends') : t('sessions.noArrangementGames') }}
          </p>
        </div>

        <div v-if="arrangement && showArrangement" class="space-y-6">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
            {{ t('sessions.arrangementTitle') }}
          </h3>

          <div class="grid gap-4 sm:grid-cols-2">
            <div
              v-for="(table, index) in arrangement.tables"
              :key="index"
              class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800"
            >
              <div class="mb-3 flex items-center justify-between">
                <h4 class="font-semibold text-gray-900 dark:text-gray-100">
                  {{ table.game.name }}
                </h4>
                <span class="rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300">
                  {{ t('sessions.table', { number: String(index + 1) }) }}
                </span>
              </div>

              <div v-if="table.game.min_players && table.game.max_players" class="mb-2 text-xs text-gray-500 dark:text-gray-400">
                {{ t('sessions.players', { min: String(table.game.min_players), max: String(table.game.max_players) }) }}
              </div>

              <ul class="mb-3 space-y-1">
                <li
                  v-for="friend in table.friends"
                  :key="friend.id"
                  class="text-sm text-gray-700 dark:text-gray-300"
                >
                  {{ friend.first_name }} {{ friend.last_name }}
                </li>
              </ul>

              <div class="border-t border-gray-100 pt-2 dark:border-gray-700">
                <span class="text-xs text-gray-500 dark:text-gray-400">
                  {{ t('sessions.avgRating') }}:
                </span>
                <span class="ml-1 text-sm font-medium text-gray-900 dark:text-gray-100">
                  {{ table.avg_rating }}/10
                </span>
              </div>
            </div>
          </div>

          <div
            v-if="arrangement.unseated.length > 0"
            class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-700 dark:bg-amber-900/20"
          >
            <h4 class="font-medium text-amber-800 dark:text-amber-300">
              {{ t('sessions.unseated') }}
            </h4>
            <p class="mt-1 text-sm text-amber-700 dark:text-amber-400">
              {{ t('sessions.unseatedDesc') }}
            </p>
            <ul class="mt-2 space-y-1">
              <li
                v-for="friend in arrangement.unseated"
                :key="friend.id"
                class="flex items-center gap-2 text-sm text-amber-800 dark:text-amber-300"
              >
                {{ friend.first_name }} {{ friend.last_name }}
                <Link
                  :href="route('preferences.show', friend.id) + '?redirect_to=' + encodeURIComponent(route('sessions.show', session.id))"
                  class="text-xs text-amber-600 underline hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-200"
                >
                  {{ t('friends.preferences') }}
                </Link>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
