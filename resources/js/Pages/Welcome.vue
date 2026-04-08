<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue'
import { useTranslate } from '@/composables/useTranslate'

const { t } = useTranslate()

defineProps<{
    canLogin: boolean
    canRegister: boolean
}>()
</script>

<template>
  <Head :title="t('welcome.title')" />

  <div class="min-h-screen overflow-x-hidden bg-gradient-to-br from-indigo-50 via-white to-indigo-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
    <nav class="flex items-center justify-between p-4 sm:px-10">
      <span class="text-xl font-bold text-indigo-600 dark:text-indigo-400">
        <span class="sm:hidden">BGA</span>
        <span class="hidden sm:inline">BoardGameApp</span>
      </span>
      <div class="flex items-center gap-2 sm:gap-3">
        <LanguageSwitcher />
        <template v-if="canLogin">
          <Link
            v-if="$page.props.auth.user"
            :href="route('dashboard')"
            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-500"
          >
            {{ t('welcome.dashboard') }}
          </Link>
          <template v-else>
            <Link
              :href="route('login')"
              class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 transition hover:text-indigo-600 dark:text-gray-300 dark:hover:text-indigo-400"
            >
              {{ t('welcome.login') }}
            </Link>
            <Link
              v-if="canRegister"
              :href="route('register')"
              class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-500"
            >
              {{ t('welcome.register') }}
            </Link>
          </template>
        </template>
      </div>
    </nav>

    <main class="mx-auto max-w-5xl px-4 py-10 text-center sm:px-6 sm:py-16">
      <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-6xl dark:text-white">
        {{ t('welcome.hero1') }}
        <span class="text-indigo-600 dark:text-indigo-400">{{ t('welcome.hero2') }}</span>
      </h1>
      <p class="mx-auto mt-6 max-w-2xl text-lg text-gray-600 dark:text-gray-400">
        {{ t('welcome.description') }}
      </p>

      <div v-if="!$page.props.auth.user" class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
        <Link
          :href="route('register')"
          class="rounded-lg bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500"
        >
          {{ t('welcome.startFree') }}
        </Link>
        <Link
          :href="route('login')"
          class="rounded-lg px-6 py-3 text-sm font-semibold text-gray-700 transition hover:text-indigo-600 dark:text-gray-300"
        >
          {{ t('welcome.haveAccount') }}
        </Link>
      </div>

      <div class="mx-auto mt-12 grid max-w-4xl gap-6 px-0 sm:grid-cols-3">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100 dark:bg-gray-800 dark:ring-gray-700">
          <div class="mx-auto flex size-12 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900/50">
            <svg class="size-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 6.087c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.036-1.007-1.875-2.25-1.875s-2.25.84-2.25 1.875c0 .369.128.713.349 1.003.215.283.401.604.401.959v0a.64.64 0 01-.657.643 48.491 48.491 0 01-4.163-.3c.186 1.613.293 3.25.315 4.907a.656.656 0 01-.658.663v0c-.355 0-.676-.186-.959-.401a1.647 1.647 0 00-1.003-.349c-1.036 0-1.875 1.007-1.875 2.25s.84 2.25 1.875 2.25c.369 0 .713-.128 1.003-.349.283-.215.604-.401.959-.401v0c.31 0 .555.26.532.57a48.039 48.039 0 01-.642 5.056c1.518.19 3.058.309 4.616.354a.64.64 0 00.657-.643v0c0-.355-.186-.676-.401-.959a1.647 1.647 0 01-.349-1.003c0-1.035 1.008-1.875 2.25-1.875 1.243 0 2.25.84 2.25 1.875 0 .369-.128.713-.349 1.003-.215.283-.4.604-.4.959v0c0 .333.277.599.61.58a48.1 48.1 0 005.427-.63 48.05 48.05 0 00.582-4.717.532.532 0 00-.533-.57v0c-.355 0-.676.186-.959.401-.29.221-.634.349-1.003.349-1.035 0-1.875-1.007-1.875-2.25s.84-2.25 1.875-2.25c.37 0 .713.128 1.003.349.283.215.604.401.96.401v0a.656.656 0 00.658-.663 48.422 48.422 0 00-.37-5.36c-1.886.342-3.81.574-5.766.689a.578.578 0 01-.61-.58v0z" />
            </svg>
          </div>
          <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">{{ t('welcome.gamesTitle') }}</h3>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ t('welcome.gamesDesc') }}</p>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100 dark:bg-gray-800 dark:ring-gray-700">
          <div class="mx-auto flex size-12 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900/50">
            <svg class="size-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
            </svg>
          </div>
          <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">{{ t('welcome.friendsTitle') }}</h3>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ t('welcome.friendsDesc') }}</p>
        </div>

        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100 dark:bg-gray-800 dark:ring-gray-700">
          <div class="mx-auto flex size-12 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900/50">
            <svg class="size-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
            </svg>
          </div>
          <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">{{ t('welcome.meetingsTitle') }}</h3>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ t('welcome.meetingsDesc') }}</p>
        </div>
      </div>
    </main>

    <footer class="pb-8 text-center text-sm text-gray-500 dark:text-gray-600">
      BoardGameApp &copy; 2026
    </footer>
  </div>
</template>
