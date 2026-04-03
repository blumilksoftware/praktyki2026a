import { describe, test, expect, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import { useConfirmDialog } from '@/composables/useConfirmDialog'

describe('ConfirmDialog', () => {
  let dialog: ReturnType<typeof useConfirmDialog>

  beforeEach(() => {
    dialog = useConfirmDialog()
    if (dialog.isOpen.value) {
      dialog.onCancel()
    }
  })

  test('is not visible when dialog is closed', () => {
    const wrapper = mount(ConfirmDialog, {
      global: { stubs: { Teleport: true } },
    })

    expect(wrapper.find('[data-testid="dialog-panel"]').exists()).toBe(false)
  })

  test('is visible when dialog is open', async () => {
    const wrapper = mount(ConfirmDialog, {
      global: { stubs: { Teleport: true } },
      attachTo: document.body,
    })

    dialog.confirm({
      title: 'Delete item',
      message: 'Are you sure you want to delete this?',
      confirmLabel: 'Delete',
      cancelLabel: 'Cancel',
      variant: 'danger',
    })

    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('Delete item')
    expect(wrapper.text()).toContain('Are you sure you want to delete this?')
  })

  test('renders the correct title and message', async () => {
    const wrapper = mount(ConfirmDialog, {
      global: { stubs: { Teleport: true } },
    })

    dialog.confirm({
      title: 'Leave page',
      message: 'You have unsaved changes.',
      confirmLabel: 'Leave',
      cancelLabel: 'Stay',
    })

    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('Leave page')
    expect(wrapper.text()).toContain('You have unsaved changes.')
  })

  test('renders the correct button labels', async () => {
    const wrapper = mount(ConfirmDialog, {
      global: { stubs: { Teleport: true } },
    })

    dialog.confirm({
      title: 'Leave page',
      message: 'You have unsaved changes.',
      confirmLabel: 'Leave',
      cancelLabel: 'Stay',
    })

    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('Leave')
    expect(wrapper.text()).toContain('Stay')
  })

  test('confirm button has red styling for danger variant', async () => {
    const wrapper = mount(ConfirmDialog, {
      global: { stubs: { Teleport: true } },
    })

    dialog.confirm({
      title: 'Delete',
      message: 'This cannot be undone.',
      confirmLabel: 'Delete',
      cancelLabel: 'Cancel',
      variant: 'danger',
    })

    await wrapper.vm.$nextTick()

    const buttons = wrapper.findAll('button')
    const confirmButton = buttons.find(b => b.text() === 'Delete')
    expect(confirmButton?.classes()).toContain('bg-red-600')
  })

  test('confirm button has indigo styling for neutral variant', async () => {
    const wrapper = mount(ConfirmDialog, {
      global: { stubs: { Teleport: true } },
    })

    dialog.confirm({
      title: 'Leave',
      message: 'You have unsaved changes.',
      confirmLabel: 'Leave',
      cancelLabel: 'Stay',
      variant: 'neutral',
    })

    await wrapper.vm.$nextTick()

    const buttons = wrapper.findAll('button')
    const confirmButton = buttons.find(b => b.text() === 'Leave')
    expect(confirmButton?.classes()).toContain('bg-indigo-600')
  })

  test('clicking the confirm button closes the dialog', async () => {
    const wrapper = mount(ConfirmDialog, {
      global: { stubs: { Teleport: true } },
    })

    const promise = dialog.confirm({
      title: 'Test',
      message: 'Test',
      confirmLabel: 'Yes',
      cancelLabel: 'No',
    })

    await wrapper.vm.$nextTick()

    const buttons = wrapper.findAll('button')
    const confirmButton = buttons.find(b => b.text() === 'Yes')
    await confirmButton?.trigger('click')

    expect(await promise).toBe(true)
    expect(dialog.isOpen.value).toBe(false)
  })

  test('clicking the cancel button closes the dialog', async () => {
    const wrapper = mount(ConfirmDialog, {
      global: { stubs: { Teleport: true } },
    })

    const promise = dialog.confirm({
      title: 'Test',
      message: 'Test',
      confirmLabel: 'Yes',
      cancelLabel: 'No',
    })

    await wrapper.vm.$nextTick()

    const buttons = wrapper.findAll('button')
    const cancelButton = buttons.find(b => b.text() === 'No')
    await cancelButton?.trigger('click')

    expect(await promise).toBe(false)
    expect(dialog.isOpen.value).toBe(false)
  })

  test('clicking the backdrop closes the dialog with false', async () => {
    const wrapper = mount(ConfirmDialog, {
      global: { stubs: { Teleport: true } },
      attachTo: document.body,
    })

    const promise = dialog.confirm({
      title: 'Test',
      message: 'Test',
      confirmLabel: 'Yes',
      cancelLabel: 'No',
    })

    await wrapper.vm.$nextTick()

    const backdrop = wrapper.find('.absolute.inset-0')
    await backdrop.trigger('click')

    expect(await promise).toBe(false)
    expect(dialog.isOpen.value).toBe(false)
  })
})
