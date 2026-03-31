import { router } from '@inertiajs/vue3'
import { useConfirmDialog } from '@/composables/useConfirmDialog'

interface DirtyTrackable {
  isDirty: boolean
}

export function useCancelWithWarning(
  form: DirtyTrackable,
  destination: string,
  t: (key: string) => string,
) {
  const { confirm } = useConfirmDialog()

  async function cancel(): Promise<void> {
    if (!form.isDirty) {
      router.visit(destination)
      return
    }

    const confirmed = await confirm({
      title: t('common.cancelTitle'),
      message: t('common.cancelMessage'),
      confirmLabel: t('common.cancelConfirmLabel'),
      cancelLabel: t('common.cancelCancelLabel'),
      variant: 'neutral',
    })

    if (confirmed) {
      router.visit(destination)
    }
  }

  return { cancel }
}
