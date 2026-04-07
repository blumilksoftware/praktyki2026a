import { config } from '@vue/test-utils'

config.global.stubs = {
  Link: { template: '<a><slot /></a>' },
  Head: { template: '<div />' },
}
