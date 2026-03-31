import { ref } from 'vue'

export interface ConfirmDialogOptions {
  title: string
  message: string
  confirmLabel: string
  cancelLabel: string
  variant?: 'danger' | 'neutral'
}

const isOpen = ref(false)
const options = ref<ConfirmDialogOptions>({
  title: '',
  message: '',
  confirmLabel: 'Confirm',
  cancelLabel: 'Cancel',
  variant: 'neutral',
})

let resolveFn: ((value: boolean) => void) | null = null

export function useConfirmDialog() {
  function confirm(opts: ConfirmDialogOptions): Promise<boolean> {
    options.value = { variant: 'neutral', ...opts }
    isOpen.value = true

    return new Promise<boolean>((resolve) => {
      resolveFn = resolve
    })
  }

  function onConfirm(): void {
    isOpen.value = false
    resolveFn?.(true)
    resolveFn = null
  }

  function onCancel(): void {
    isOpen.value = false
    resolveFn?.(false)
    resolveFn = null
  }

  return { isOpen, options, confirm, onConfirm, onCancel }
}
