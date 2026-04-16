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

interface DuplicateMatch {
  id: number
  name: string
  match_type: 'name' | 'email'
  email?: string
}

const duplicateMatch = ref<DuplicateMatch | null>(null)
const showDuplicateDialog = ref(false)
const duplicateChecking = ref(false)

const form = useForm({
  first_name: '',
  last_name: '',
  email: '',
})

const { cancel } = useCancelWithWarning(form, route('friends.index'), t)

async function runDuplicateCheck(): Promise<void> {
  if (!form.first_name.trim() || !form.last_name.trim()) return

  duplicateChecking.value = true
  try {
    const { data } = await axios.post(route('friends.checkDuplicate'), {
      first_name: form.first_name,
      last_name: form.last_name,
      email: form.email || null,
    })
    if (data.duplicate) {
      duplicateMatch.value = data.duplicate
      showDuplicateDialog.value = true
    }
  } finally {
    duplicateChecking.value = false
  }
}

function onLastNameBlur(): void {
  runDuplicateCheck()
}

function onEmailBlur(): void {
  if (form.email.trim()) runDuplicateCheck()
}

function saveAnyway(): void {
  showDuplicateDialog.value = false
  duplicateMatch.value = null
}

function cancelDuplicate(): void {
  router.visit(route('friends.index'))
}

function submit(): void {
  if (duplicateMatch.value) {
    showDuplicateDialog.value = true
    return
  }
  form.post(route('friends.store'))
}
</script>

<template>
  <Head :title="t('friends.addTitle')" />

  <AuthenticatedLayout>
    <template #header>
      <h2
        class="text-xl leading-tight font-semibold text-gray-800 dark:text-gray-200"
      >
        {{ t('friends.addTitle') }}
      </h2>
    </template>

    <Teleport to="body">
      <div
        v-if="showDuplicateDialog && duplicateMatch"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
      >
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-800">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
            {{ t('friends.duplicateTitle') }}
          </h3>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            <template v-if="duplicateMatch.match_type === 'name'">
              {{ t('friends.duplicateNameMessage').replace('{name}', duplicateMatch.name) }}
            </template>
            <template v-else>
              {{ t('friends.duplicateEmailMessage').replace('{email}', duplicateMatch.email ?? '') }}
            </template>
          </p>
          <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
            <button
              type="button"
              class="cursor-pointer rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
              @click="cancelDuplicate"
            >
              {{ t('friends.cancel') }}
            </button>
            <PrimaryButton type="button" @click="saveAnyway">
              {{ t('friends.duplicateSaveAnyway') }}
            </PrimaryButton>
          </div>
        </div>
      </div>
    </Teleport>

    <div class="py-6 sm:py-12">
      <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
        <div
          class="bg-white p-4 shadow-sm sm:rounded-lg sm:p-6 dark:bg-gray-800"
        >
          <form class="space-y-6" @submit.prevent="submit">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <div>
                <InputLabel for="first_name" :value="t('friends.firstName')" />
                <TextInput
                  id="first_name"
                  v-model="form.first_name"
                  type="text"
                  class="mt-1 block w-full"
                  :invalid="!!form.errors.first_name"
                  autofocus
                />
                <InputError :message="form.errors.first_name" class="mt-2" />
              </div>

              <div>
                <InputLabel for="last_name" :value="t('friends.lastName')" />
                <TextInput
                  id="last_name"
                  v-model="form.last_name"
                  type="text"
                  class="mt-1 block w-full"
                  :invalid="!!form.errors.last_name"
                  @blur="onLastNameBlur"
                />
                <InputError :message="form.errors.last_name" class="mt-2" />
                <p v-if="duplicateChecking" class="mt-1 text-xs text-gray-400">
                  {{ t('friends.duplicateChecking') }}
                </p>
              </div>
            </div>

            <div>
              <InputLabel for="email" :value="t('friends.emailOptional')" />
              <TextInput
                id="email"
                v-model="form.email"
                type="email"
                class="mt-1 block w-full"
                :invalid="!!form.errors.email"
                @blur="onEmailBlur"
              />
              <InputError :message="form.errors.email" class="mt-2" />
            </div>

            <div class="justify-end gap-4 flex items-center">
              <button
                type="button"
                class="cursor-pointer text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                @click="cancel"
              >
                {{ t('friends.cancel') }}
              </button>
              <PrimaryButton :disabled="form.processing">
                {{ t('friends.save') }}
              </PrimaryButton>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
