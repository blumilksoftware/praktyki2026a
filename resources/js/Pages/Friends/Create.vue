<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
})

function submit() {
    form.post(route('friends.store'))
}
</script>

<template>
    <Head title="Dodaj znajomego" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Dodaj znajomego
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="first_name" value="Imie" />
                                <TextInput
                                    id="first_name"
                                    v-model="form.first_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    autofocus
                                />
                                <InputError :message="form.errors.first_name" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="last_name" value="Nazwisko" />
                                <TextInput
                                    id="last_name"
                                    v-model="form.last_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.last_name" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <InputLabel for="email" value="Email (opcjonalny)" />
                            <TextInput
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.email" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-4">
                            <PrimaryButton :disabled="form.processing">Zapisz</PrimaryButton>
                            <Link
                                :href="route('friends.index')"
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
