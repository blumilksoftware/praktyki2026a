<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
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
    sessions: Session[]
}>()

function deleteSession(session: Session) {
  const translations = (usePage().props as Record<string, unknown>).translations as Record<string, string>
  const msg = (translations?.['sessions.deleteConfirm'] ?? 'Delete "{name}"?').replace('{name}', session.name)
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
          <div v-if="sessions.length === 0" class="p-4 sm:p-6 text-gray-500 dark:text-gray-400">
            {{ t('sessions.empty') }}
          </div>

          <template v-else>
            <table class="hidden w-full text-left text-sm sm:table">
              <thead class="border-b bg-gray-50 text-xs uppercase text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                <tr>
                  <th class="px-6 py-3">{{ t('sessions.name') }}</th>
                  <th class="px-6 py-3">{{ t('sessions.date') }}</th>
                  <th class="px-6 py-3">{{ t('sessions.friends') }}</th>
                  <th class="px-6 py-3">{{ t('sessions.games') }}</th>
                  <th class="px-6 py-3 text-right">{{ t('sessions.actions') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="session in sessions"
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

            <div class="divide-y dark:divide-gray-700 sm:hidden">
              <div
                v-for="session in sessions"
                :key="session.id"
                class="p-4 space-y-2"
              >
                <div class="flex items-center justify-between">
                  <Link
                    :href="route('sessions.show', session.id)"
                    class="font-medium text-indigo-600 hover:text-indigo-900 dark:text-indigo-400"
                  >
                    {{ session.name }}
                  </Link>
                  <span class="text-sm text-gray-500 dark:text-gray-400">{{ session.date }}</span>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  {{ session.friends.length }} {{ t('sessions.friendsCount') }}, {{ session.games.length }} {{ t('sessions.gamesCount') }}
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
          </template>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
