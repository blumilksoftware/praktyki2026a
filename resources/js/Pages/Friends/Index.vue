<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'

interface Friend {
    id: number
    first_name: string
    last_name: string
    email: string | null
}

defineProps<{
    friends: Friend[]
}>()

function deleteFriend(friend: Friend) {
    if (confirm(`Czy na pewno chcesz usunac "${friend.first_name} ${friend.last_name}"?`)) {
        router.delete(route('friends.destroy', friend.id))
    }
}
</script>

<template>
    <Head title="Znajomi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Znajomi
                </h2>
                <Link
                    :href="route('friends.create')"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500"
                >
                    Dodaj znajomego
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div v-if="friends.length === 0" class="p-6 text-gray-500 dark:text-gray-400">
                        Nie masz jeszcze zadnych znajomych. Dodaj pierwszego!
                    </div>

                    <table v-else class="w-full text-left text-sm">
                        <thead class="border-b bg-gray-50 text-xs uppercase text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                            <tr>
                                <th class="px-6 py-3">Imie i nazwisko</th>
                                <th class="px-6 py-3">Email</th>
                                <th class="px-6 py-3 text-right">Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="friend in friends"
                                :key="friend.id"
                                class="border-b dark:border-gray-700"
                            >
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">
                                    {{ friend.first_name }} {{ friend.last_name }}
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                    {{ friend.email || '—' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Link
                                        :href="route('preferences.show', friend.id)"
                                        class="mr-3 text-indigo-600 hover:text-indigo-900 dark:text-indigo-400"
                                    >
                                        Preferencje
                                    </Link>
                                    <Link
                                        :href="route('friends.edit', friend.id)"
                                        class="mr-3 text-indigo-600 hover:text-indigo-900 dark:text-indigo-400"
                                    >
                                        Edytuj
                                    </Link>
                                    <button
                                        class="text-red-600 hover:text-red-900 dark:text-red-400"
                                        @click="deleteFriend(friend)"
                                    >
                                        Usun
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
