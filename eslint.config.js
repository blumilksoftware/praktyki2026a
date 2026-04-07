import blumilkTypescript from '@blumilksoftware/eslint-config/typescript-config.js'

export default [
  ...blumilkTypescript,
  {
    ignores: ['resources/js/ziggy.js', 'resources/js/Types/ziggy.d.ts'],
  },
]
