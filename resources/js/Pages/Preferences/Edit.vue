<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

interface Game {
    id: number
    name: string
    min_players: number
    max_players: number
    pivot?: { rating: number }
}

interface Friend {
    id: number
    first_name: string
    last_name: string
    games: Game[]
}

const props = defineProps<{
    friend: Friend
    games: Game[]
}>()

const existingRatings = computed(() => {
    const map: Record<number, number> = {}
    for (const game of props.friend.games) {
        if (game.pivot) {
            map[game.id] = game.pivot.rating
        }
    }
    return map
})

const form = useForm({
    ratings: props.games.map(game => ({
        game_id: game.id,
        rating: existingRatings.value[game.id] || 0,
    })),
})

function submit() {
    const filtered = {
        ratings: form.ratings.filter(r => r.rating > 0),
    }
    form.transform(() => filtered).put(route('preferences.update', props.friend.id))
}

function getRating(gameId: number): number {
    return form.ratings.find(r => r.game_id === gameId)?.rating || 0
}

function setRating(gameId: number, rating: number) {
    const entry = form.ratings.find(r => r.game_id === gameId)
    if (entry) {
        entry.rating = entry.rating === rating ? 0 : rating
    }
}
</script>

<template>
    <Head :title="`Preferencje — ${friend.first_name} ${friend.last_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Preferencje — {{ friend.first_name }} {{ friend.last_name }}
                </h2>
                <Link
                    :href="route('friends.index')"
                    class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                >
                    &larr; Wstecz
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <p v-if="games.length === 0" class="text-gray-500 dark:text-gray-400">
                        Brak dostepnych gier. Najpierw dodaj gry do swojej kolekcji.
                    </p>

                    <form v-else @submit.prevent="submit" class="space-y-4">
                        <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">
                            Kliknij gwiazdki aby ocenic gry od 1 do 10. Kliknij ponownie aby usunac ocene.
                        </p>

                        <div
                            v-for="game in games"
                            :key="game.id"
                            class="flex items-center justify-between rounded-lg border border-gray-100 p-4 dark:border-gray-700"
                        >
                            <div>
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ game.name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ game.min_players }}–{{ game.max_players }} graczy</p>
                            </div>
                            <div class="flex items-center gap-1">
                                <button
                                    v-for="n in 10"
                                    :key="n"
                                    type="button"
                                    class="h-6 w-6 rounded text-xs font-medium transition"
                                    :class="getRating(game.id) >= n
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-gray-100 text-gray-400 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-500 dark:hover:bg-gray-600'"
                                    @click="setRating(game.id, n)"
                                >
                                    {{ n }}
                                </button>
                            </div>
                        </div>

                        <div class="pt-4">
                            <PrimaryButton :disabled="form.processing">Zapisz preferencje</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
