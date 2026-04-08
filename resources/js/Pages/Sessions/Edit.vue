<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { useTranslate } from '@/composables/useTranslate'
import { useCancelWithWarning } from '@/composables/useCancelWithWarning'

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

const props = defineProps<{
  session: Session
  friends: Friend[]
  games: Game[]
}>()

const form = useForm({
  name: props.session.name,
  date: props.session.date?.substring(0, 10) ?? '',
  notes: props.session.notes ?? '',
  friend_ids: props.session.friends.map(f => f.id),
  game_ids: props.session.games.map(g => g.id),
})

const { cancel } = useCancelWithWarning(form, route('sessions.index'), t)

function toggleFriend(id: number): void {
  const index = form.friend_ids.indexOf(id)
  if (index === -1) {
    form.friend_ids.push(id)
  } else {
    form.friend_ids.splice(index, 1)
  }
}

function toggleGame(id: number): void {
  const index = form.game_ids.indexOf(id)
  if (index === -1) {
    form.game_ids.push(id)
  } else {
    form.game_ids.splice(index, 1)
  }
}

function submit(): void {
  form.put(route('sessions.update', props.session.id))
}
</script>

<template>
  <Head :title="t('sessions.editTitle')" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
        {{ t('sessions.editTitle') }}
      </h2>
    </template>

    <div class="py-6 sm:py-12">
      <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
        <div class="bg-white p-4 shadow-sm sm:rounded-lg sm:p-6 dark:bg-gray-800">
          <form class="space-y-6" @submit.prevent="submit">
            <div>
              <InputLabel for="name" :value="t('sessions.name')" />
              <TextInput
                id="name"
                v-model="form.name"
                type="text"
                class="mt-1 block w-full"
                :invalid="!!form.errors.name"
                autofocus
              />
              <InputError :message="form.errors.name" class="mt-2" />
            </div>

            <div>
              <InputLabel for="date" :value="t('sessions.date')" />
              <TextInput
                id="date"
                v-model="form.date"
                type="date"
                lang="pl"
                class="mt-1 block w-full"
                :invalid="!!form.errors.date"
              />
              <InputError :message="form.errors.date" class="mt-2" />
            </div>

            <div>
              <InputLabel for="notes" :value="t('sessions.notes')" />
              <textarea
                id="notes"
                v-model="form.notes"
                rows="3"
                class="mt-1 block w-full rounded-md shadow-sm focus:ring-indigo-500 dark:bg-gray-900 dark:text-gray-300"
                :class="form.errors.notes ? 'border-red-500 focus:border-red-500' : 'border-gray-300 focus:border-indigo-500 dark:border-gray-700'"
              />
              <InputError :message="form.errors.notes" class="mt-2" />
            </div>

            <div>
              <InputLabel :value="t('sessions.selectFriends')" />
              <div v-if="props.friends.length === 0" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                {{ t('sessions.noFriends') }}
              </div>
              <div v-else class="mt-2 space-y-2">
                <label
                  v-for="friend in props.friends"
                  :key="friend.id"
                  class="flex cursor-pointer items-center gap-2"
                >
                  <input
                    type="checkbox"
                    :checked="form.friend_ids.includes(friend.id)"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900"
                    @change="toggleFriend(friend.id)"
                  >
                  <span class="text-sm text-gray-700 dark:text-gray-300">
                    {{ friend.first_name }} {{ friend.last_name }}
                  </span>
                </label>
              </div>
              <InputError :message="form.errors.friend_ids" class="mt-2" />
            </div>

            <div>
              <InputLabel :value="t('sessions.selectGames')" />
              <div v-if="props.games.length === 0" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                {{ t('sessions.noGames') }}
              </div>
              <div v-else class="mt-2 space-y-2">
                <label
                  v-for="game in props.games"
                  :key="game.id"
                  class="flex cursor-pointer items-center gap-2"
                >
                  <input
                    type="checkbox"
                    :checked="form.game_ids.includes(game.id)"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900"
                    @change="toggleGame(game.id)"
                  >
                  <span class="text-sm text-gray-700 dark:text-gray-300">
                    {{ game.name }}
                  </span>
                </label>
              </div>
              <InputError :message="form.errors.game_ids" class="mt-2" />
            </div>

            <div class="flex items-center justify-end gap-4">
              <button
                type="button"
                class="cursor-pointer text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                @click="cancel"
              >
                {{ t('sessions.cancel') }}
              </button>
              <PrimaryButton :disabled="form.processing">
                {{ t('sessions.save') }}
              </PrimaryButton>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
