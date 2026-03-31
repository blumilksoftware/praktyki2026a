<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useTranslate } from '@/composables/useTranslate'
import { useCancelWithWarning } from '@/composables/useCancelWithWarning'

const { t } = useTranslate()

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
  redirectTo?: string
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

function submit(): void {
  const filtered = {
    ratings: form.ratings.filter(r => r.rating > 0),
    redirect_to: props.redirectTo,
  }
  form.transform(() => filtered).put(route('preferences.update', props.friend.id))
}

const { cancel } = useCancelWithWarning(form, route('friends.index'), t)

function getRating(gameId: number): number {
  return form.ratings.find(r => r.game_id === gameId)?.rating || 0
}

function setRating(gameId: number, rating: number): void {
  const entry = form.ratings.find(r => r.game_id === gameId)
  if (entry) {
    entry.rating = entry.rating === rating ? 0 : rating
  }
}
</script>

<template>
  <Head :title="t('preferences.title', { name: `${friend.first_name} ${friend.last_name}` })" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
        {{ t('preferences.title', { name: `${friend.first_name} ${friend.last_name}` }) }}
      </h2>
    </template>

    <div class="py-6 sm:py-12">
      <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <div class="bg-white p-4 shadow-sm sm:rounded-lg sm:p-6 dark:bg-gray-800">
          <p v-if="games.length === 0" class="text-gray-500 dark:text-gray-400">
            {{ t('preferences.noGames') }}
          </p>

          <form v-else class="space-y-4" @submit.prevent="submit">
            <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">
              {{ t('preferences.hint') }}
            </p>

            <div
              v-for="game in games"
              :key="game.id"
              class="space-y-2 rounded-lg border border-gray-100 p-3 sm:flex sm:items-center sm:justify-between sm:space-y-0 sm:p-4 dark:border-gray-700"
            >
              <div class="min-w-0">
                <p class="font-medium text-gray-900 dark:text-gray-100">{{ game.name }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                  {{ game.min_players }}–{{ game.max_players }} {{ t('preferences.playersCount') }}
                </p>
              </div>
              <div class="flex flex-wrap items-center gap-1">
                <button
                  v-for="n in 10"
                  :key="n"
                  type="button"
                  class="size-6 cursor-pointer rounded text-xs font-medium transition"
                  :class="getRating(game.id) >= n
                    ? 'bg-indigo-600 text-white'
                    : 'bg-gray-100 text-gray-400 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-500 dark:hover:bg-gray-600'"
                  @click="setRating(game.id, n)"
                >
                  {{ n }}
                </button>
              </div>
            </div>

            <div class="flex items-center justify-end gap-4 pt-4">
              <button
                type="button"
                class="cursor-pointer text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                @click="cancel"
              >
                {{ t('preferences.cancel') }}
              </button>
              <PrimaryButton :disabled="form.processing">
                {{ t('preferences.save') }}
              </PrimaryButton>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
