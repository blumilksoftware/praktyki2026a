<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { useTranslate } from '@/composables/useTranslate'

const { t } = useTranslate()

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
    const translations = (usePage().props as Record<string, unknown>).translations as Record<string, string>
    const msg = (translations?.['games.deleteConfirm'] ?? 'Delete "{name}"?').replace('{name}', game.name)
    if (confirm(msg)) {
        router.delete(route('games.destroy', game.id))
    }
}
</script>

<template>
    <Head :title="t('games.title')" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    {{ t('games.title') }}
                </h2>
                <Link
                    :href="route('games.create')"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500"
                >
                    {{ t('games.add') }}
                </Link>
            </div>
        </template>

        <div class="py-6 sm:py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div v-if="games.length === 0" class="p-4 sm:p-6 text-gray-500 dark:text-gray-400">
                        {{ t('games.empty') }}
                    </div>

                    <template v-else>
                        <table class="hidden w-full text-left text-sm sm:table">
                            <thead class="border-b bg-gray-50 text-xs uppercase text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                                <tr>
                                    <th class="px-6 py-3">{{ t('games.name') }}</th>
                                    <th class="px-6 py-3">{{ t('games.players') }}</th>
                                    <th class="px-6 py-3">{{ t('games.type') }}</th>
                                    <th class="px-6 py-3 text-right">{{ t('games.actions') }}</th>
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
                                            {{ t('games.shared') }}
                                        </span>
                                        <span
                                            v-else
                                            class="rounded bg-blue-100 px-2 py-1 text-xs text-blue-800 dark:bg-blue-900 dark:text-blue-300"
                                        >
                                            {{ t('games.mine') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <template v-if="!game.is_shared">
                                            <Link
                                                :href="route('games.edit', game.id)"
                                                class="mr-3 text-indigo-600 hover:text-indigo-900 dark:text-indigo-400"
                                            >
                                                {{ t('games.edit') }}
                                            </Link>
                                            <button
                                                class="text-red-600 hover:text-red-900 dark:text-red-400"
                                                @click="deleteGame(game)"
                                            >
                                                {{ t('games.delete') }}
                                            </button>
                                        </template>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="divide-y dark:divide-gray-700 sm:hidden">
                            <div
                                v-for="game in games"
                                :key="game.id"
                                class="p-4 space-y-2"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ game.name }}</span>
                                    <span
                                        v-if="game.is_shared"
                                        class="rounded bg-green-100 px-2 py-1 text-xs text-green-800 dark:bg-green-900 dark:text-green-300"
                                    >
                                        {{ t('games.shared') }}
                                    </span>
                                    <span
                                        v-else
                                        class="rounded bg-blue-100 px-2 py-1 text-xs text-blue-800 dark:bg-blue-900 dark:text-blue-300"
                                    >
                                        {{ t('games.mine') }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ game.min_players }}–{{ game.max_players }} {{ t('games.playersCount') }}</p>
                                <div v-if="!game.is_shared" class="flex gap-4 pt-1">
                                    <Link
                                        :href="route('games.edit', game.id)"
                                        class="text-sm font-medium text-indigo-600 hover:text-indigo-900 dark:text-indigo-400"
                                    >
                                        {{ t('games.edit') }}
                                    </Link>
                                    <button
                                        class="text-sm font-medium text-red-600 hover:text-red-900 dark:text-red-400"
                                        @click="deleteGame(game)"
                                    >
                                        {{ t('games.delete') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
