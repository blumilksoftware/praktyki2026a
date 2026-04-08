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
  email: string | null
}

const props = defineProps<{
  friend: Friend
}>()

const form = useForm({
  first_name: props.friend.first_name,
  last_name: props.friend.last_name,
  email: props.friend.email || '',
})

const { cancel } = useCancelWithWarning(form, route('friends.index'), t)

function submit(): void {
  form.put(route('friends.update', props.friend.id))
}
</script>

<template>
  <Head :title="t('friends.editTitle')" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
        {{ t('friends.editTitle') }}
      </h2>
    </template>

    <div class="py-6 sm:py-12">
      <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
        <div class="bg-white p-4 shadow-sm sm:rounded-lg sm:p-6 dark:bg-gray-800">
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
                />
                <InputError :message="form.errors.last_name" class="mt-2" />
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
              />
              <InputError :message="form.errors.email" class="mt-2" />
            </div>

            <div class="justify-end gap-4 flex items-center ">
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
