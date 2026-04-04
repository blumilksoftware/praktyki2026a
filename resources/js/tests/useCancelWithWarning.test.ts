import { describe, test, expect, vi, beforeEach } from 'vitest'
import { useCancelWithWarning } from '@/composables/useCancelWithWarning'
import { useConfirmDialog } from '@/composables/useConfirmDialog'

vi.mock('@inertiajs/vue3', () => ({
  router: {
    visit: vi.fn(),
  },
}))

import { router } from '@inertiajs/vue3'

describe('useCancelWithWarning', () => {
  let dialog: ReturnType<typeof useConfirmDialog>

  beforeEach(() => {
    vi.clearAllMocks()
    dialog = useConfirmDialog()
    if (dialog.isOpen.value) {
      dialog.onCancel()
    }
  })

  const t = (key: string) => key

  test('navigates immediately when form is not dirty', async () => {
    const form = { isDirty: false }
    const { cancel } = useCancelWithWarning(form, '/games', t)

    await cancel()

    expect(router.visit).toHaveBeenCalledWith('/games')
    expect(dialog.isOpen.value).toBe(false)
  })

  test('navigates to the correct destination when form is not dirty', async () => {
    const form = { isDirty: false }
    const { cancel } = useCancelWithWarning(form, '/friends', t)

    await cancel()

    expect(router.visit).toHaveBeenCalledWith('/friends')
  })

  test('does not open the dialog when form is not dirty', async () => {
    const form = { isDirty: false }
    const { cancel } = useCancelWithWarning(form, '/games', t)

    await cancel()

    expect(dialog.isOpen.value).toBe(false)
  })

  test('opens the dialog when form is dirty', async () => {
    const form = { isDirty: true }
    const { cancel } = useCancelWithWarning(form, '/games', t)

    cancel()

    await new Promise(resolve => setTimeout(resolve, 0))

    expect(dialog.isOpen.value).toBe(true)
  })

  test('navigates when user confirms leaving a dirty form', async () => {
    const form = { isDirty: true }
    const { cancel } = useCancelWithWarning(form, '/games', t)

    const cancelPromise = cancel()
    await new Promise(resolve => setTimeout(resolve, 0))

    dialog.onConfirm()
    await cancelPromise

    expect(router.visit).toHaveBeenCalledWith('/games')
  })

  test('does not navigate when user cancels the dialog', async () => {
    const form = { isDirty: true }
    const { cancel } = useCancelWithWarning(form, '/games', t)

    const cancelPromise = cancel()
    await new Promise(resolve => setTimeout(resolve, 0))

    dialog.onCancel()
    await cancelPromise

    expect(router.visit).not.toHaveBeenCalled()
  })

  test('closes the dialog after user chooses to stay', async () => {
    const form = { isDirty: true }
    const { cancel } = useCancelWithWarning(form, '/games', t)

    const cancelPromise = cancel()
    await new Promise(resolve => setTimeout(resolve, 0))

    dialog.onCancel()
    await cancelPromise

    expect(dialog.isOpen.value).toBe(false)
  })
})
