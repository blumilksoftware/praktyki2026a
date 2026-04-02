import { describe, test, expect, vi, beforeEach } from 'vitest'
import { useCancelWithWarning } from '@/composables/useCancelWithWarning'
import { useConfirmDialog } from '@/composables/useConfirmDialog'

// We mock the router so navigation calls don't actually try to do
// anything — we just want to verify they were called with the right URL.
vi.mock('@inertiajs/vue3', () => ({
  router: {
    visit: vi.fn(),
  },
}))

// Import the mocked router so we can assert on it
import { router } from '@inertiajs/vue3'

describe('useCancelWithWarning', () => {
  let dialog: ReturnType<typeof useConfirmDialog>

  beforeEach(() => {
    // Reset the router mock between tests
    vi.clearAllMocks()
    // Reset dialog state
    dialog = useConfirmDialog()
    if (dialog.isOpen.value) {
      dialog.onCancel()
    }
  })

  // A minimal translate function that returns the key — good enough for tests
  const t = (key: string) => key

  // ── Happy path: form is clean ─────────────────────────────────────────────

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

  // ── Happy path: form is dirty, user confirms leaving ─────────────────────

  test('opens the dialog when form is dirty', async () => {
    const form = { isDirty: true }
    const { cancel } = useCancelWithWarning(form, '/games', t)

    // Don't await — we need to interact with the dialog before it resolves
    cancel()

    // Give the promise a tick to open the dialog
    await new Promise(resolve => setTimeout(resolve, 0))

    expect(dialog.isOpen.value).toBe(true)
  })

  test('navigates when user confirms leaving a dirty form', async () => {
    const form = { isDirty: true }
    const { cancel } = useCancelWithWarning(form, '/games', t)

    const cancelPromise = cancel()
    await new Promise(resolve => setTimeout(resolve, 0))

    // User clicks "Leave"
    dialog.onConfirm()
    await cancelPromise

    expect(router.visit).toHaveBeenCalledWith('/games')
  })

  // ── Sad path: form is dirty, user chooses to stay ─────────────────────────

  test('does not navigate when user cancels the dialog', async () => {
    const form = { isDirty: true }
    const { cancel } = useCancelWithWarning(form, '/games', t)

    const cancelPromise = cancel()
    await new Promise(resolve => setTimeout(resolve, 0))

    // User clicks "Stay"
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
