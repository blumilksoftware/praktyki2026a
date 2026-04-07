import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

export function useTranslate() {
  const page = usePage()

  const translations = computed(() => {
    return (page.props as Record<string, unknown>).translations as Record<string, string> ?? {}
  })

  function t(key: string, replacements: Record<string, string> = {}): string {
    let value = translations.value[key] ?? key

    for (const [search, replace] of Object.entries(replacements)) {
      value = value.replace(`{${search}}`, replace)
    }

    return value
  }

  return { t }
}
