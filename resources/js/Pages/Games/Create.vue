<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { useTranslate } from '@/composables/useTranslate'
import { ref, computed } from 'vue'
import axios from 'axios'

const { t } = useTranslate()

// ── Mode toggle ────────────────────────────────────────────────────────────
// 'manual' = existing hand-entry form
// 'bgg'    = new BGG URL import flow
type Mode = 'manual' | 'bgg'
const mode = ref<Mode>('manual')

// ── Manual form ────────────────────────────────────────────────────────────
const form = useForm({
  name: '',
  min_players: 2,
  max_players: 4,
})

function submit() {
  form.post(route('games.store'))
}

// ── BGG import ─────────────────────────────────────────────────────────────
const bggUrl        = ref('')
const bggLoading    = ref(false)
const bggError      = ref<string | null>(null)
const bggFieldError = ref<string | null>(null)
const bggPreview    = ref<null | {
  name: string
  min_players: number
  max_players: number
  year: number | null
  min_age: number | null
  description: string
  bgg_id: number
  bgg_url: string
}>(null)

// BGG URLs always contain /boardgame/{digits}
const looksLikeBggUrl = computed(() =>
  /boardgamegeek\.com\/boardgame\/\d+/i.test(bggUrl.value),
)

async function fetchFromBgg() {
  if (!looksLikeBggUrl.value || bggLoading.value) return

  bggLoading.value    = true
  bggError.value      = null
  bggFieldError.value = null
  bggPreview.value    = null

  try {
    const { data } = await axios.post(route('games.importFromBgg'), {
      url: bggUrl.value,
    })
    bggPreview.value = data.game
  } catch (e: any) {
    if (e.response?.status === 422) {
      bggFieldError.value = e.response.data.message
    } else if (e.response?.status === 502) {
      bggError.value = e.response.data.message
    } else {
      bggError.value = t('games.bggGenericError')
    }
  } finally {
    bggLoading.value = false
  }
}

function resetBgg() {
  bggUrl.value        = ''
  bggPreview.value    = null
  bggError.value      = null
  bggFieldError.value = null
}

const confirmForm = useForm({
  name: '',
  min_players: 2,
  max_players: 4,
  bgg_id: null as number | null,
  bgg_url: '',
  description: '',
  year: null as number | null,
  min_age: null as number | null,
})

function confirmBggImport() {
  if (!bggPreview.value) return

  confirmForm.name        = bggPreview.value.name
  confirmForm.min_players = bggPreview.value.min_players
  confirmForm.max_players = bggPreview.value.max_players
  confirmForm.bgg_id      = bggPreview.value.bgg_id
  confirmForm.bgg_url     = bggPreview.value.bgg_url
  confirmForm.description = bggPreview.value.description
  confirmForm.year        = bggPreview.value.year
  confirmForm.min_age     = bggPreview.value.min_age

  confirmForm.post(route('games.store'))
}

const bggFieldState = computed(() => {
  if (bggFieldError.value)                    return 'border-red-400 focus:ring-red-400'
  if (looksLikeBggUrl.value)                  return 'border-green-400 focus:ring-green-400'
  if (bggUrl.value && !looksLikeBggUrl.value) return 'border-yellow-400 focus:ring-yellow-400'
  return 'border-gray-300 dark:border-gray-600'
})
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
        <div class="bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
          <!-- ── Mode toggle tabs ──────────────────────────────── -->
          <div class="flex border-b border-gray-200 dark:border-gray-700">
            <button
              type="button"
              class="flex-1 px-4 py-3 text-sm font-medium transition-colors focus:outline-none"
              :class="mode === 'manual'
                ? 'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400'
                : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
              @click="mode = 'manual'"
            >
              <span class="mr-2 inline-block">✍️</span>
              {{ t('games.addManually') }}
            </button>
            <button
              type="button"
              class="flex-1 px-4 py-3 text-sm font-medium transition-colors focus:outline-none"
              :class="mode === 'bgg'
                ? 'border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400'
                : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
              @click="mode = 'bgg'; resetBgg()"
            >
              <span class="mr-2 inline-block">🤓</span>
              {{ t('games.importFromBgg') }}
            </button>
          </div>

          <div class="p-4 sm:p-6">
            <!-- ─────────────────── MANUAL FORM ───────────────────── -->
            <form
              v-if="mode === 'manual'"
              class="space-y-6"
              @submit.prevent="submit"
            >
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
                <PrimaryButton :disabled="form.processing">
                  {{ t('games.save') }}
                </PrimaryButton>
                <Link
                  :href="route('games.index')"
                  class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                >
                  {{ t('games.cancel') }}
                </Link>
              </div>
            </form>

            <!-- ── BGG IMPORT FLOW ────────────────────────────── -->
            <div v-else class="space-y-6">
              <template v-if="!bggPreview">
                <!-- BGG attribution logo — required by BGG API terms -->
                <a
                  href="https://boardgamegeek.com"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="inline-block opacity-90 transition-opacity hover:opacity-100"
                  :title="t('games.bggAttributionTitle')"
                >
                  <img
                    src="/images/powered_by_BGG.png"
                    alt="Powered by BoardGameGeek"
                    class="h-7 w-auto dark:brightness-90"
                  >
                </a>

                <div>
                  <InputLabel for="bgg_url" :value="t('games.bggUrl')" />
                  <div class="relative mt-1">
                    <TextInput
                      id="bgg_url"
                      v-model="bggUrl"
                      type="url"
                      class="block w-full pr-10 font-mono text-sm"
                      :class="bggFieldState"
                      :placeholder="t('games.bggUrlPlaceholder')"
                      :disabled="bggLoading"
                      autofocus
                      @keydown.enter.prevent="fetchFromBgg"
                    />
                    <span
                      v-if="looksLikeBggUrl"
                      class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-green-500"
                    >✓</span>
                  </div>

                  <p v-if="bggFieldError" class="mt-2 text-sm text-red-500">
                    {{ bggFieldError }}
                  </p>
                  <p
                    v-else-if="bggUrl && !looksLikeBggUrl"
                    class="mt-2 text-xs text-yellow-600 dark:text-yellow-400"
                  >
                    {{ t('games.bggUrlHint') }}
                  </p>
                </div>

                <div
                  v-if="bggError"
                  class="flex items-start gap-3 rounded-md bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/30 dark:text-red-400"
                >
                  <span class="mt-0.5 shrink-0">⚠</span>
                  {{ bggError }}
                </div>

                <div class="flex items-center gap-4">
                  <PrimaryButton
                    type="button"
                    :disabled="!looksLikeBggUrl || bggLoading"
                    @click="fetchFromBgg"
                  >
                    <svg
                      v-if="bggLoading"
                      class="-ml-1 mr-2 size-4 animate-spin"
                      fill="none" viewBox="0 0 24 24"
                    >
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                    </svg>
                    {{ bggLoading ? t('games.bggFetching') : t('games.bggFetch') }}
                  </PrimaryButton>

                  <Link
                    :href="route('games.index')"
                    class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                  >
                    {{ t('games.cancel') }}
                  </Link>
                </div>

                <details class="group rounded-lg border border-gray-200 dark:border-gray-700">
                  <summary class="cursor-pointer select-none px-4 py-3 text-sm font-medium text-gray-600 dark:text-gray-400">
                    {{ t('games.bggHowToFind') }}
                  </summary>
                  <ol class="space-y-1 px-6 pb-4 pt-2 text-sm text-gray-500 dark:text-gray-400 list-decimal">
                    <li>{{ t('games.bggStep1') }}</li>
                    <li>{{ t('games.bggStep2') }}</li>
                    <li>{{ t('games.bggStep3') }}</li>
                    <li>
                      {{ t('games.bggStep4') }}<br>
                      <code class="mt-1 inline-block rounded bg-gray-100 px-2 py-0.5 text-xs dark:bg-gray-700">
                        https://boardgamegeek.com/boardgame/174430/gloomhaven
                      </code>
                    </li>
                  </ol>
                </details>
              </template>

              <template v-else>
                <div class="flex items-center gap-2 rounded-md bg-green-50 px-4 py-3 text-sm font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">
                  <span>✓</span>
                  {{ t('games.bggFound') }}
                </div>

                <div class="rounded-lg border border-gray-200 p-5 dark:border-gray-700">
                  <div class="flex items-start justify-between gap-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                      {{ bggPreview.name }}
                    </h3>
                    <div class="flex shrink-0 flex-col items-end gap-2">
                      <a
                        href="https://boardgamegeek.com"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="opacity-80 transition-opacity hover:opacity-100"
                      >
                        <img
                          src="/images/powered_by_BGG.png"
                          alt="Powered by BoardGameGeek"
                          class="h-5 w-auto dark:brightness-90"
                        >
                      </a>
                      <a
                        :href="bggPreview.bgg_url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="text-xs text-indigo-500 hover:underline"
                      >
                        {{ t('games.bggViewOnBgg') }} ↗
                      </a>
                    </div>
                  </div>

                  <!-- Meta chips -->
                  <div class="mt-3 flex flex-wrap gap-2">
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                      👥 {{ bggPreview.min_players }}–{{ bggPreview.max_players }} {{ t('games.players') }}
                    </span>
                    <span v-if="bggPreview.year" class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                      📅 {{ bggPreview.year }}
                    </span>
                    <span v-if="bggPreview.min_age" class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                      🔞 {{ t('games.age') }} {{ bggPreview.min_age }}+
                    </span>
                  </div>

                  <p
                    v-if="bggPreview.description"
                    class="mt-3 line-clamp-3 text-sm text-gray-500 dark:text-gray-400"
                  >
                    {{ bggPreview.description }}
                  </p>
                </div>

                <div class="flex items-center gap-4">
                  <PrimaryButton
                    type="button"
                    @click="confirmBggImport"
                  >
                    {{ t('games.bggConfirm') }}
                  </PrimaryButton>
                  <button
                    type="button"
                    class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
                    @click="resetBgg"
                  >
                    {{ t('games.bggTryAnother') }}
                  </button>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
