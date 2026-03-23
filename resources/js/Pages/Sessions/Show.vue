<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { useTranslate } from '@/composables/useTranslate'

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

defineProps<{
    session: Session
}>()
</script>

<template>
  <Head :title="session.name" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
          {{ session.name }}
        </h2>
        <Link
          :href="route('sessions.index')"
          class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
        >
          {{ t('sessions.back') }}
        </Link>
      </div>
    </template>

    <div class="py-6 sm:py-12">
      <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white p-4 shadow-sm sm:rounded-lg sm:p-6 dark:bg-gray-800">
          <div class="space-y-4">
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('sessions.date') }}</p>
              <p class="mt-1 text-gray-900 dark:text-gray-100">{{ session.date }}</p>
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
              class="text-sm text-gray-700 dark:text-gray-300"
            >
              {{ friend.first_name }} {{ friend.last_name }}
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
      </div>
    </div>
  </AuthenticatedLayout>
</template>
