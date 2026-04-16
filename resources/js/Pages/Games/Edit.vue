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
import { useCancelWithWarning } from '@/composables/useCancelWithWarning'

const { t } = useTranslate()

interface Game {
  id: number
  name: string
  min_players: number
  max_players: number
  description: string | null
  year: number | null
  copies: number
}

interface DuplicateMatch {
  id: number
  name: string
  copies: number
  is_shared: boolean
}

const props = defineProps<{
  game: Game
}>()

const duplicateMatch = ref<DuplicateMatch | null>(null)
const showDuplicateDialog = ref(false)
const duplicateChecking = ref(false)

const form = useForm({
  name: props.game.name,
  min_players: props.game.min_players,
  max_players: props.game.max_players,
  description: props.game.description ?? '',
  year: props.game.year,
  copies: props.game.copies,
})

const { cancel } = useCancelWithWarning(form, route('games.index'), t)

async function checkDuplicate(name: string): Promise<DuplicateMatch | null> {
  if (!name.trim()) return null

  duplicateChecking.value = true
  try {
    const { data } = await axios.post(route('games.checkDuplicate'), {
      name,
      exclude_id: props.game.id,
    })
    return data.duplicate ?? null
  } catch {
    return null
  } finally {
    duplicateChecking.value = false
  }
}

async function onNameBlur(): Promise<void> {
  const match = await checkDuplicate(form.name)
  if (match) {
    duplicateMatch.value = match
    showDuplicateDialog.value = true
  } else {
    duplicateMatch.value = null
  }
}

function keepSeparate(): void {
  showDuplicateDialog.value = false
  duplicateMatch.value = null
}

function cancelDuplicate(): void {
  router.visit(route('games.index'))
}

function mergeInto(): void {
  if (!duplicateMatch.value) return
  router.post(route('games.mergeInto', props.game.id), {
    target_id: duplicateMatch.value.id,
  })
  showDuplicateDialog.value = false
}

function submit(): void {
  if (duplicateMatch.value) {
    showDuplicateDialog.value = true
    return
  }
  form.put(route('games.update', props.game.id))
}
</script>

<template>
  <Head :title="t('games.editTitle')" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
        {{ t('games.editTitle') }}
      </h2>
    </template>

    <Teleport to="body">
      <div
        v-if="showDuplicateDialog && duplicateMatch"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
      >
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-800">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
            {{ t('games.duplicateTitle') }}
          </h3>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            {{ t('games.duplicateMessage').replace('{name}', duplicateMatch.name) }}
          </p>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ t('games.duplicateCopies').replace('{count}', String(duplicateMatch.copies)) }}
          </p>
          <div class="mt-6 flex flex-col gap-3">
            <PrimaryButton type="button" class="w-full justify-center" @click="mergeInto">
              {{ t('games.duplicateMerge').replace('{count}', String(form.copies)) }}
            </PrimaryButton>
            <button
              type="button"
              class="cursor-pointer rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
              @click="keepSeparate"
            >
              {{ t('games.duplicateSaveNew') }}
            </button>
            <button
              type="button"
              class="cursor-pointer rounded-md px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
              @click="cancelDuplicate"
            >
              {{ t('games.cancel') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <div class="py-6 sm:py-12">
      <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
        <div class="bg-white p-4 shadow-sm sm:rounded-lg sm:p-6 dark:bg-gray-800">
          <form class="space-y-6" novalidate @submit.prevent="submit">
            <div>
              <InputLabel for="name" :value="t('games.name')" />
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
                {{ t('games.duplicateChecking') }}
              </p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <div>
                <InputLabel for="min_players" :value="t('games.minPlayers')" />
                <TextInput
                  id="min_players"
                  v-model.number="form.min_players"
                  type="number"
                  min="1"
                  class="mt-1 block w-full"
                  :invalid="!!form.errors.min_players"
                />
                <InputError :message="form.errors.min_players" class="mt-2" />
              </div>
              <div>
                <InputLabel for="max_players" :value="t('games.maxPlayers')" />
                <TextInput
                  id="max_players"
                  v-model.number="form.max_players"
                  type="number"
                  min="1"
                  class="mt-1 block w-full"
                  :invalid="!!form.errors.max_players"
                />
                <InputError :message="form.errors.max_players" class="mt-2" />
              </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <div>
                <InputLabel for="year" :value="t('games.year')" />
                <TextInput
                  id="year"
                  v-model.number="form.year"
                  type="number"
                  min="1800"
                  :max="new Date().getFullYear()"
                  class="mt-1 block w-full"
                  :invalid="!!form.errors.year"
                />
                <InputError :message="form.errors.year" class="mt-2" />
              </div>
              <div>
                <InputLabel for="copies" :value="t('games.copies')" />
                <TextInput
                  id="copies"
                  v-model.number="form.copies"
                  type="number"
                  min="1"
                  class="mt-1 block w-full"
                  :invalid="!!form.errors.copies"
                />
                <InputError :message="form.errors.copies" class="mt-2" />
              </div>
            </div>

            <div>
              <InputLabel for="description" :value="t('games.description')" />
              <textarea
                id="description"
                v-model="form.description"
                rows="4"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 sm:text-sm"
              />
              <InputError :message="form.errors.description" class="mt-2" />
            </div>

            <div class="flex items-center justify-end gap-4">
              <button
                type="button"
                class="cursor-pointer text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                @click="cancel"
              >
                {{ t('games.cancel') }}
              </button>
              <PrimaryButton :disabled="form.processing">
                {{ t('games.save') }}
              </PrimaryButton>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
