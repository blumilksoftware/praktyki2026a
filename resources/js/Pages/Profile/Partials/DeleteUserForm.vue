<script setup>
import DangerButton from '@/Components/DangerButton.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import Modal from '@/Components/Modal.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { useForm } from '@inertiajs/vue3'
import { nextTick, ref } from 'vue'
import { useTranslate } from '@/composables/useTranslate'

const { t } = useTranslate()

const confirmingUserDeletion = ref(false)
const passwordInput = ref(null)

const form = useForm({
  password: '',
})

const confirmUserDeletion = () => {
  confirmingUserDeletion.value = true
  nextTick(() => passwordInput.value.focus())
}

const deleteUser = () => {
  form.delete(route('profile.destroy'), {
    preserveScroll: true,
    onSuccess: () => closeModal(),
    onError: () => passwordInput.value.focus(),
    onFinish: () => form.reset(),
  })
}

const closeModal = () => {
  confirmingUserDeletion.value = false
  form.clearErrors()
  form.reset()
}
</script>

<template>
  <section class="space-y-6">
    <header>
      <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
        {{ t('profile.deleteAccount') }}
      </h2>

      <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        {{ t('profile.deleteAccountDesc') }}
      </p>
    </header>

    <DangerButton @click="confirmUserDeletion">{{ t('profile.deleteAccount') }}</DangerButton>

    <Modal :show="confirmingUserDeletion" @close="closeModal">
      <div class="p-6">
        <h2
          class="text-lg font-medium text-gray-900 dark:text-gray-100"
        >
          {{ t('profile.deleteAccountConfirm') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
          {{ t('profile.deleteAccountConfirmDesc') }}
        </p>

        <div class="mt-6">
          <InputLabel
            for="password"
            :value="t('auth.password')"
            class="sr-only"
          />

          <TextInput
            id="password"
            ref="passwordInput"
            v-model="form.password"
            type="password"
            class="mt-1 block w-3/4"
            :placeholder="t('auth.password')"
            @keyup.enter="deleteUser"
          />

          <InputError :message="form.errors.password" class="mt-2" />
        </div>

        <div class="mt-6 flex justify-end">
          <SecondaryButton @click="closeModal">
            {{ t('profile.cancel') }}
          </SecondaryButton>

          <DangerButton
            class="ms-3"
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
            @click="deleteUser"
          >
            {{ t('profile.deleteAccount') }}
          </DangerButton>
        </div>
      </div>
    </Modal>
  </section>
</template>
