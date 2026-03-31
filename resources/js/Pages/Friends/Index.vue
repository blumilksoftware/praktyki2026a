<script setup lang="ts">
import IconButton from '@/Components/IconButton.vue'
import { useTranslate } from '@/composables/useTranslate'
import { useConfirmDialog } from '@/composables/useConfirmDialog'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { IconEdit, IconTrash, IconAdjustmentsAlt } from '@tabler/icons-vue'

const { t } = useTranslate()
const { confirm } = useConfirmDialog()

interface Friend {
  id: number
  first_name: string
  last_name: string
  email: string | null
}

defineProps<{
  friends: Friend[]
}>()

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
        <h2 class="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200">
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
          <div
            v-if="friends.length === 0"
            class="p-4 text-gray-500 sm:p-6 dark:text-gray-400"
          >
            {{ t('friends.empty') }}
          </div>

          <template v-else>
            <table class="hidden w-full text-left text-sm sm:table">
              <thead class="border-b bg-gray-50 text-xs text-gray-700 uppercase dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
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
                  class="border-b border-gray-100 transition hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/50"
                >
                  <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">
                    {{ friend.first_name }} {{ friend.last_name }}
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
                v-for="friend in friends"
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
          </template>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
