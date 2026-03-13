<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'

interface Game {
    id: number
    name: string
    min_players: number
    max_players: number
    is_shared: boolean
    user_id: number | null
}

defineProps<{
    games: Game[]
}>()

function deleteGame(game: Game) {
    if (confirm(`Czy na pewno chcesz usunąć "${game.name}"?`)) {
        router.delete(route('games.destroy', game.id))
    }
}
</script>

<template>
    <Head title="Gry" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Gry
                </h2>
                <Link
                    :href="route('games.create')"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500"
                >
                    Dodaj grę
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div v-if="games.length === 0" class="p-6 text-gray-500 dark:text-gray-400">
                        Nie masz jeszcze żadnych gier. Dodaj pierwszą!
                    </div>

                    <table v-else class="w-full text-left text-sm">
                        <thead class="border-b bg-gray-50 text-xs uppercase text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                            <tr>
                                <th class="px-6 py-3">Nazwa</th>
                                <th class="px-6 py-3">Gracze</th>
                                <th class="px-6 py-3">Typ</th>
                                <th class="px-6 py-3 text-right">Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="game in games"
                                :key="game.id"
                                class="border-b dark:border-gray-700"
                            >
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">
                                    {{ game.name }}
                                </td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400">
                                    {{ game.min_players }}–{{ game.max_players }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        v-if="game.is_shared"
                                        class="rounded bg-green-100 px-2 py-1 text-xs text-green-800 dark:bg-green-900 dark:text-green-300"
                                    >
                                        Wspólna
                                    </span>
                                    <span
                                        v-else
                                        class="rounded bg-blue-100 px-2 py-1 text-xs text-blue-800 dark:bg-blue-900 dark:text-blue-300"
                                    >
                                        Moja
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <template v-if="!game.is_shared">
                                        <Link
                                            :href="route('games.edit', game.id)"
                                            class="mr-3 text-indigo-600 hover:text-indigo-900 dark:text-indigo-400"
                                        >
                                            Edytuj
                                        </Link>
                                        <button
                                            class="text-red-600 hover:text-red-900 dark:text-red-400"
                                            @click="deleteGame(game)"
                                        >
                                            Usuń
                                        </button>
                                    </template>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
