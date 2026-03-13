<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

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
    <Head title="Dodaj gre" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Dodaj gre
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <InputLabel for="name" value="Nazwa" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                autofocus
                            />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="min_players" value="Min graczy" />
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
                                <InputLabel for="max_players" value="Max graczy" />
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
                            <PrimaryButton :disabled="form.processing">Zapisz</PrimaryButton>
                            <Link
                                :href="route('games.index')"
                                class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                            >
                                Anuluj
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
