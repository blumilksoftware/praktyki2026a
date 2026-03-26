<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { useTranslate } from '@/composables/useTranslate'

const { t } = useTranslate()
</script>

<template>
  <Head title="Seating" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
        Seating Planner
      </h2>
    </template>

    <div class="py-6 sm:py-12">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg dark:bg-gray-800">
          <p class="text-gray-500 dark:text-gray-400">
            Seating planner is coming soon.
          </p>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
