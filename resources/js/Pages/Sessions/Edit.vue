<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import axios from 'axios'
import { useTranslate } from '@/composables/useTranslate'
import { useFormatDate } from '@/composables/useFormatDate'
import { useCancelWithWarning } from '@/composables/useCancelWithWarning'

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
}

interface Session {
  id: number
  name: string
  date: string
  notes: string | null
  friends: Friend[]
  games: Game[]
}

interface DuplicateMatch {
  id: number
  name: string
  date: string
}

const props = defineProps<{
  session: Session
  friends: Friend[]
  games: Game[]
}>()

const duplicateMatch = ref<DuplicateMatch | null>(null)
const showDuplicateDialog = ref(false)
const duplicateChecking = ref(false)

const form = useForm({
  name: props.session.name,
  date: props.session.date?.substring(0, 10) ?? '',
  notes: props.session.notes ?? '',
  friend_ids: props.session.friends.map(f => f.id),
  game_ids: props.session.games.map(g => g.id),
})

const { cancel } = useCancelWithWarning(form, route('sessions.index'), t)

async function runDuplicateCheck(): Promise<void> {
  if (!form.name.trim() || !form.date) return

  duplicateChecking.value = true
  try {
    const { data } = await axios.post(route('sessions.checkDuplicate'), {
      name: form.name,
      date: form.date,
      exclude_id: props.session.id,
    })
    if (data.duplicate) {
      duplicateMatch.value = data.duplicate
      showDuplicateDialog.value = true
    } else {
      duplicateMatch.value = null
    }
  } finally {
    duplicateChecking.value = false
  }
}

function onNameBlur(): void {
  runDuplicateCheck()
}

function onDateChange(): void {
  if (form.name.trim()) runDuplicateCheck()
}

function saveAnyway(): void {
  showDuplicateDialog.value = false
  duplicateMatch.value = null
}

function cancelDuplicate(): void {
  router.visit(route('sessions.index'))
}

function submit(): void {
  if (duplicateMatch.value) {
    showDuplicateDialog.value = true
    return
  }
  form.put(route('sessions.update', props.session.id))
}

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
</script>

<template>
  <Head :title="t('sessions.editTitle')" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
        {{ t('sessions.editTitle') }}
      </h2>
    </template>

    <Teleport to="body">
      <div
        v-if="showDuplicateDialog && duplicateMatch"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
      >
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-800">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
            {{ t('sessions.duplicateTitle') }}
          </h3>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            {{
              t('sessions.duplicateMessage')
                .replace('{name}', duplicateMatch.name)
                .replace('{date}', formatDate(duplicateMatch.date))
            }}
          </p>
          <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
            <button
              type="button"
              class="cursor-pointer rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
              @click="cancelDuplicate"
            >
              {{ t('sessions.cancel') }}
            </button>
            <PrimaryButton type="button" @click="saveAnyway">
              {{ t('sessions.duplicateSaveAnyway') }}
            </PrimaryButton>
          </div>
        </div>
      </div>
    </Teleport>

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
                @blur="onNameBlur"
              />
              <InputError :message="form.errors.name" class="mt-2" />
              <p v-if="duplicateChecking" class="mt-1 text-xs text-gray-400">
                {{ t('sessions.duplicateChecking') }}
              </p>
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
                @change="onDateChange"
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
