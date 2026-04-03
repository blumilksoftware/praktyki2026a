import { describe, test, expect, beforeEach } from 'vitest'
import { useConfirmDialog } from '@/composables/useConfirmDialog'

describe('useConfirmDialog', () => {
  let dialog: ReturnType<typeof useConfirmDialog>

  beforeEach(() => {
    dialog = useConfirmDialog()
    if (dialog.isOpen.value) {
      dialog.onCancel()
    }
  })

  test('dialog is closed by default', () => {
    expect(dialog.isOpen.value).toBe(false)
  })

  test('confirm() opens the dialog', async () => {
    dialog.confirm({
      title: 'Delete item',
      message: 'Are you sure?',
      confirmLabel: 'Delete',
      cancelLabel: 'Cancel',
    })

    expect(dialog.isOpen.value).toBe(true)
  })

  test('confirm() sets the dialog options', async () => {
    dialog.confirm({
      title: 'Delete item',
      message: 'Are you sure?',
      confirmLabel: 'Delete',
      cancelLabel: 'Cancel',
      variant: 'danger',
    })

    expect(dialog.options.value.title).toBe('Delete item')
    expect(dialog.options.value.message).toBe('Are you sure?')
    expect(dialog.options.value.confirmLabel).toBe('Delete')
    expect(dialog.options.value.cancelLabel).toBe('Cancel')
    expect(dialog.options.value.variant).toBe('danger')
  })

  test('variant defaults to neutral when not specified', async () => {
    dialog.confirm({
      title: 'Leave page',
      message: 'You have unsaved changes.',
      confirmLabel: 'Leave',
      cancelLabel: 'Stay',
    })

    expect(dialog.options.value.variant).toBe('neutral')
  })

  test('onConfirm() closes the dialog', async () => {
    dialog.confirm({
      title: 'Test',
      message: 'Test',
      confirmLabel: 'Yes',
      cancelLabel: 'No',
    })

    dialog.onConfirm()

    expect(dialog.isOpen.value).toBe(false)
  })

  test('onConfirm() resolves the promise with true', async () => {
    const promise = dialog.confirm({
      title: 'Test',
      message: 'Test',
      confirmLabel: 'Yes',
      cancelLabel: 'No',
    })

    dialog.onConfirm()

    expect(await promise).toBe(true)
  })

  test('onCancel() closes the dialog', async () => {
    dialog.confirm({
      title: 'Test',
      message: 'Test',
      confirmLabel: 'Yes',
      cancelLabel: 'No',
    })

    dialog.onCancel()

    expect(dialog.isOpen.value).toBe(false)
  })

  test('onCancel() resolves the promise with false', async () => {
    const promise = dialog.confirm({
      title: 'Test',
      message: 'Test',
      confirmLabel: 'Yes',
      cancelLabel: 'No',
    })

    dialog.onCancel()

    expect(await promise).toBe(false)
  })

  test('opening a second dialog after the first was resolved works correctly', async () => {
    const first = dialog.confirm({
      title: 'First',
      message: 'First dialog',
      confirmLabel: 'Yes',
      cancelLabel: 'No',
    })
    dialog.onConfirm()
    await first

    const second = dialog.confirm({
      title: 'Second',
      message: 'Second dialog',
      confirmLabel: 'Yes',
      cancelLabel: 'No',
    })

    expect(dialog.isOpen.value).toBe(true)
    expect(dialog.options.value.title).toBe('Second')

    dialog.onCancel()
    expect(await second).toBe(false)
  })
})
