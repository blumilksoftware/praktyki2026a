import { router } from '@inertiajs/vue3'

interface DirtyTrackable {
  isDirty: boolean
}

export function useCancelWithWarning(
  form: DirtyTrackable,
  destination: string,
  t: (key: string) => string,
) {
  function cancel(): void {
    if (!form.isDirty) {
      router.visit(destination)
      return
    }

    if (confirm(t('common.cancelConfirm'))) {
      router.visit(destination)
    }
  }

  return { cancel }
}
