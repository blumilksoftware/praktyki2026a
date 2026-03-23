<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { useTranslate } from '@/composables/useTranslate'

const { t } = useTranslate()

defineProps<{
    gamesCount: number
    friendsCount: number
    sessionsCount: number
}>()
</script>

<template>
  <Head :title="t('dashboard.title')" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
        {{ t('dashboard.title') }}
      </h2>
    </template>

    <div class="py-6 sm:py-12">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-8 overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg dark:bg-gray-800">
          <p class="text-lg text-gray-900 dark:text-gray-100">
            {{ t('dashboard.welcome') }} <span class="font-semibold">{{ $page.props.auth.user.name }}</span>!
          </p>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <div class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg dark:bg-gray-800">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('dashboard.games') }}</p>
                <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ gamesCount }}</p>
              </div>
              <div class="flex size-12 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900/50">
                <svg class="size-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 6.087c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.036-1.007-1.875-2.25-1.875s-2.25.84-2.25 1.875c0 .369.128.713.349 1.003.215.283.401.604.401.959v0a.64.64 0 01-.657.643 48.491 48.491 0 01-4.163-.3c.186 1.613.293 3.25.315 4.907a.656.656 0 01-.658.663v0c-.355 0-.676-.186-.959-.401a1.647 1.647 0 00-1.003-.349c-1.036 0-1.875 1.007-1.875 2.25s.84 2.25 1.875 2.25c.369 0 .713-.128 1.003-.349.283-.215.604-.401.959-.401v0c.31 0 .555.26.532.57a48.039 48.039 0 01-.642 5.056c1.518.19 3.058.309 4.616.354a.64.64 0 00.657-.643v0c0-.355-.186-.676-.401-.959a1.647 1.647 0 01-.349-1.003c0-1.035 1.008-1.875 2.25-1.875 1.243 0 2.25.84 2.25 1.875 0 .369-.128.713-.349 1.003-.215.283-.4.604-.4.959v0c0 .333.277.599.61.58a48.1 48.1 0 005.427-.63 48.05 48.05 0 00.582-4.717.532.532 0 00-.533-.57v0c-.355 0-.676.186-.959.401-.29.221-.634.349-1.003.349-1.035 0-1.875-1.007-1.875-2.25s.84-2.25 1.875-2.25c.37 0 .713.128 1.003.349.283.215.604.401.96.401v0a.656.656 0 00.658-.663 48.422 48.422 0 00-.37-5.36c-1.886.342-3.81.574-5.766.689a.578.578 0 01-.61-.58v0z" />
                </svg>
              </div>
            </div>
            <Link
              :href="route('games.create')"
              class="mt-4 inline-block text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
            >
              {{ t('dashboard.addGame') }}
            </Link>
          </div>

          <div class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg dark:bg-gray-800">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('dashboard.friends') }}</p>
                <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ friendsCount }}</p>
              </div>
              <div class="flex size-12 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900/50">
                <svg class="size-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
              </div>
            </div>
            <Link
              :href="route('friends.create')"
              class="mt-4 inline-block text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
            >
              {{ t('dashboard.addFriend') }}
            </Link>
          </div>

          <div class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg dark:bg-gray-800">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('dashboard.sessions') }}</p>
                <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ sessionsCount }}</p>
              </div>
              <div class="flex size-12 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900/50">
                <svg class="size-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
              </div>
            </div>
            <Link
              :href="route('sessions.create')"
              class="mt-4 inline-block text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
            >
              {{ t('dashboard.addSession') }}
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
