<script setup lang="ts">
import { useConfirmDialog } from '@/composables/useConfirmDialog'


const { isOpen, options, onConfirm, onCancel } = useConfirmDialog()
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition ease-out duration-150"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-100"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @keydown.esc="onCancel"
      >
        <div
          class="absolute inset-0 bg-black/50"
          @click="onCancel"
        />

        <Transition
          enter-active-class="transition ease-out duration-150"
          enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="transition ease-in duration-100"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-95"
        >
          <div
            v-if="isOpen"
            class="relative w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-800"
          >
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
              {{ options.title }}
            </h3>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
              {{ options.message }}
            </p>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
              <button
                type="button"
                class="cursor-pointer rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-1 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                @click="onCancel"
              >
                {{ options.cancelLabel }}
              </button>
              <button
                type="button"
                class="cursor-pointer rounded-md px-4 py-2 text-sm font-medium text-white transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1"
                :class="options.variant === 'danger'
                  ? 'bg-red-600 hover:bg-red-700 focus:ring-red-500'
                  : 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500'"
                @click="onConfirm"
              >
                {{ options.confirmLabel }}
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>
