<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { useTranslate } from '@/composables/useTranslate'

const { t } = useTranslate()

interface Friend {
    id: number
    first_name: string
    last_name: string
    email: string | null
}

defineProps<{
    friends: Friend[]
}>()

function deleteFriend(friend: Friend) {
  const translations = (usePage().props as Record<string, unknown>).translations as Record<string, string>
  const msg = (translations?.['friends.deleteConfirm'] ?? 'Delete "{name}"?').replace('{name}', `${friend.first_name} ${friend.last_name}`)
  if (confirm(msg)) {
    router.delete(route('friends.destroy', friend.id))
  }
}
</script>

<template>
  <Head :title="t('friends.title')" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
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
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
          <div v-if="friends.length === 0" class="p-4 sm:p-6 text-gray-500 dark:text-gray-400">
            {{ t('friends.empty') }}
          </div>

          <template v-else>
            <table class="hidden w-full text-left text-sm sm:table">
              <thead class="border-b bg-gray-50 text-xs uppercase text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                <tr>
                  <th class="px-6 py-3">{{ t('friends.fullName') }}</th>
                  <th class="px-6 py-3">{{ t('friends.email') }}</th>
                  <th class="px-6 py-3 text-right">{{ t('friends.actions') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="friend in friends"
                  :key="friend.id"
                  class="border-b transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/50"
                >
                  <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">
                    {{ friend.first_name }} {{ friend.last_name }}
                  </td>
                  <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                    {{ friend.email || '—' }}
                  </td>
                  <td class="px-6 py-4 text-right">
                    <Link
                      :href="route('preferences.show', friend.id)"
                      class="mr-3 text-indigo-600 hover:text-indigo-500 hover:underline dark:text-indigo-400 dark:hover:text-indigo-300"
                    >
                      {{ t('friends.preferences') }}
                    </Link>
                    <Link
                      :href="route('friends.edit', friend.id)"
                      class="mr-3 text-indigo-600 hover:text-indigo-500 hover:underline dark:text-indigo-400 dark:hover:text-indigo-300"
                    >
                      {{ t('friends.edit') }}
                    </Link>
                    <button
                      class="text-red-600 hover:text-red-500 hover:underline dark:text-red-400 dark:hover:text-red-300"
                      @click="deleteFriend(friend)"
                    >
                      {{ t('friends.delete') }}
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>

            <div class="divide-y dark:divide-gray-700 sm:hidden">
              <div
                v-for="friend in friends"
                :key="friend.id"
                class="p-4 space-y-2"
              >
                <p class="font-medium text-gray-900 dark:text-gray-100">{{ friend.first_name }} {{ friend.last_name }}</p>
                <p v-if="friend.email" class="text-sm text-gray-600 dark:text-gray-400">{{ friend.email }}</p>
                <div class="flex gap-4 pt-1">
                  <Link
                    :href="route('preferences.show', friend.id)"
                    class="text-sm font-medium text-indigo-600 hover:text-indigo-500 hover:underline dark:text-indigo-400 dark:hover:text-indigo-300"
                  >
                    {{ t('friends.preferences') }}
                  </Link>
                  <Link
                    :href="route('friends.edit', friend.id)"
                    class="text-sm font-medium text-indigo-600 hover:text-indigo-500 hover:underline dark:text-indigo-400 dark:hover:text-indigo-300"
                  >
                    {{ t('friends.edit') }}
                  </Link>
                  <button
                    class="text-sm font-medium text-red-600 hover:text-red-500 hover:underline dark:text-red-400 dark:hover:text-red-300"
                    @click="deleteFriend(friend)"
                  >
                    {{ t('friends.delete') }}
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
