<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useTranslate } from '@/composables/useTranslate'

const { t } = useTranslate()

const form = useForm({
    name: '',
    min_players: 2,
    max_players: 4,
})

function submit() {
    form.post(route('games.store'))
}
</script>

<template>
    <Head :title="t('games.addTitle')" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                {{ t('games.addTitle') }}
            </h2>
        </template>

        <div class="py-6 sm:py-12">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow-sm sm:rounded-lg sm:p-6 dark:bg-gray-800">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <InputLabel for="name" :value="t('games.name')" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                autofocus
                            />
                            <InputError :message="form.errors.name" class="mt-2" />
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
                                />
                                <InputError :message="form.errors.max_players" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <PrimaryButton :disabled="form.processing">{{ t('games.save') }}</PrimaryButton>
                            <Link
                                :href="route('games.index')"
                                class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                            >
                                {{ t('games.cancel') }}
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
